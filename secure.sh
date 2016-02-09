#! /bin/bash

owner="`id -nu`"

cat <<EOM

PHPki is designed to be an easy to use "certificate factory" requiring minimum human intervention to administer. It is intended for use within a trusted INTRAnet for the creation and management of x.509 e-mail digital certificates by departmental managers. IT IS NOT INTENDED FOR USE OVER THE INTERNET.

This application stores private keys within a sub-directory, making them potentially susceptible to compromise. Extra care has been taken in the design of this application to protect the security of your certificates, on the condition that you INSTALL IT AS THE ROOT USER.  However, no software is 100% secure.  

Please run this script from INSIDE the application folder and AFTER running setup.php. Default values will be displayed inside [].

Also please note that Apache mod_ssl configuration is REQUIRED (as requested by the SSLRequireSSL directive).

EOM

read -p "Enter the location of your PHPki password [/etc/.phpkipasswd]: " passwd_file
passwd_file=${passwd_file:-"/etc/.phpkipasswd"}
echo

read -p "Enter the location of your PHPki groups file [/etc/.phpkigroups]: " groups_file
groups_file=${groups_file:-"/etc/.phpkigroups"}

if [[ ! -f "$groups_file" ]]; then
	echo
	echo "The user groups file does not yet exist, attempting to create empty file at specified location."
	` > $groups_file`
	#echo > $groups_file
	echo
	echo "If you already have a htpasswd file, please manually add the users in it to the htgroups file."
   	
fi

if [[ ! -f "$passwd_file" ]]; then
    echo
    echo "The password file you specified does not yet exist."
    echo "Let's create it and add your first user."
    echo
    read -p "Enter a user id: " user_id
    read -p "Choose a group for $user_id:
    \"admin\" if you want to give the user full access,
    \"[cert-manager]\" if the user can create and manage certs, but can't create/delete users under the admin panel and can't run PHPki setup. `echo $'\n> '`" user_group
    if [[ $user_group == "" || $user_group == "cert-manager" || $user_group == "admin" ]]; then
    	user_group=${user_group:-"cert-manager"}
    else
    	user_group="cert-manager"
    fi
    
    echo "Creating the user account for $user_id as $user_group..."
    htpasswd -c "$passwd_file" "$user_id" || exit

    echo "Checking if user is in $groups_file, otherwise adding them..."
    
    # Removing empty lines from file
    sed '/^$/d' $groups_file > $groups_file.out
  	mv  $groups_file.out $groups_file
    
    #if [[ ${user_group} == "cert-manager" || ${user_group} == "regular-user" ||  ${user_group} == "admin" ]]; then
    if [[ ${user_group} == "cert-manager" ||  ${user_group} == "admin" ]]; then
    	temp=`cat $groups_file | grep $user_group:`
	    if [[ ${temp} == "" ]]; then
	        echo "$user_group: $user_id" >> $groups_file
	    else 
	        temp=`cat $groups_file | grep -E "${user_group}:.*${user_id}\s"`       
	        if [[ ${temp} == "" ]]; then
	            sed -i "/^${user_group}:/ s/$/ ${user_id}/" $groups_file
	        fi
	    fi
    else echo "Error: Wrong user group entered, skipping. Please add user to the appropriate group(s) manually or re-run this script."
    fi

    echo
    echo "Creating the administrator account and adding it to $groups_file..."
	echo "See the README file for more information about the 'pkiadmin' user."

    temp=`cat $groups_file | grep admin:`
    if [[ ${temp} == "" ]]; then
        echo "admin: pkiadmin" >> $groups_file
    else
        temp=`cat $groups_file | grep -E "admin:.*pkiadmin\s"`
        if [[ ${temp} == "" ]]; then
            sed -i '/^admin:/ s/$/ pkiadmin/' $groups_file
        fi
        temp=`cat $groups_file | grep -E "cert-manager:.*pkiadmin\s"`
        if [[ ${temp} == "" ]]; then
            sed -i '/^cert-manager:/ s/$/ pkiadmin/' $groups_file
        fi
    fi

    htpasswd "$passwd_file" 'pkiadmin' || exit
fi

if [[ ! "${owner}_" = "root_" ]]; then
	cat <<EOM
YOU ARE NOT LOGGED ON AS ROOT!

If you choose to proceed anyway, and you plan to make this application
available over the Internet, you increase the risk of compromising the
security of your certifcates and your server.  

This script may not run correctly if you are not the ROOT user.
EOM
fi

echo
echo -n "Enter the user ID your web server runs as (apache, www-data etc.) [www-data]: " ; read -r x
echo
echo -n "Enter the group ID your web server runs as (apache, www-data etc.) [www-data]: " ; read -r z
echo
echo "Enter the IP(s) or subnet address required for users to be allowed access to folder ./admin. Your value will be appended to '127.0.0.1 ::1' (localhost is always allowed)."; 
echo -n "Enter IP(s) (multiple values should be separated by space) [192.168.0.0/16]: "; read -r y
echo
echo -n "If you'd also like to restrict access to the ./ca and ./openvpn folders based on IP or subnet, please enter the permitted address(es) (your value will be appended to '127.0.0.1 ::1'); otherwise leave empty: `echo $'\n'`" ; read -r w

user=${x:-"www-data"}
group=${z:-"www-data"}
subnet_admin=${y:-'192.168.0.0/16'}
subnet_admin="${subnet_admin} 127.0.0.1 ::1"
subnet_general=${w:-''}

echo
echo "Setting read-write permissions for $group over $passwd_file and $groups_file..."
chown $owner:$group $passwd_file $groups_file
chmod 760 $passwd_file $groups_file

echo
echo "Writing htaccess files..."

for i in ./include; do
	echo "Require all denied" >$i/.htaccess
done 

cat <<EOS > ./admin/.htaccess 
<RequireAll>
	SSLRequireSSL
	AuthName "Restricted Area"
	AuthType Basic
	AuthUserFile "$passwd_file"
	AuthGroupFile "$groups_file"
	Require valid-user
	Require ip $subnet_admin
	Require group admin
</RequireAll>

EOS

if [[ "$subnet_general" != "" ]]; then
cat <<EOS > ./ca/.htaccess
<RequireAll>
	SSLRequireSSL
	AuthName "Restricted Area"
	AuthType Basic
	AuthUserFile "$passwd_file"
	AuthGroupFile "$groups_file"
	Require valid-user
	Require group admin cert-manager
	Require ip $subnet_general 127.0.0.1 ::1
</RequireAll>

EOS

cat <<EOS > ./openvpn/.htaccess
<RequireAll>
	SSLRequireSSL
	AuthName "Restricted Area"
	AuthType Basic
	AuthUserFile "$passwd_file"
	AuthGroupFile "$groups_file"
	Require valid-user
	Require group admin cert-manager
	Require ip $subnet_general 127.0.0.1 ::1
</RequireAll>

EOS

else
cat <<EOS > ./ca/.htaccess
<RequireAll>
	SSLRequireSSL
	AuthName "Restricted Area"
	AuthType Basic
	AuthUserFile "$passwd_file"
	AuthGroupFile "$groups_file"
	Require valid-user
	Require group admin cert-manager
</RequireAll>

EOS

cat <<EOS > ./openvpn/.htaccess
<RequireAll>
	SSLRequireSSL
	AuthName "Restricted Area"
	AuthType Basic
	AuthUserFile "$passwd_file"
	AuthGroupFile "$groups_file"
	Require valid-user
	Require group admin cert-manager
</RequireAll> 

EOS
fi

echo
echo "Writing permissions to PHPki web directory..."

# Start with web server getting read-only access to everything.
# Directories have sticky bits set.
find .           -exec chown $owner:$group {} \;
find .   -type l -exec chown -h $owner:$group {} \;
find . ! -type d -exec chmod 640 {} \;
find .   -type d -exec chmod 3750 {} \;

# Display file list with new permissions

list_files=`ls -la --color .`
#echo "$list_files"

echo
echo "Now we will secure the storage directory."
read -p "Please enter storage directory path [/var/www/phpki-store]: " -r storage_dir
storage_dir=${storage_dir:-"/var/www/phpki-store"}

# Secure the storage directory

# Check if other users are in the web server group
#another_user=`egrep ^${group} '/etc/group' | awk -F':' '{print $4}'`
apache_group=`getent group ${group}`
echo
echo "Users in group ${group} will receive rw permissions on storage folder. Group contents: $apache_group"

# Directories have sticky bits set.
find $storage_dir           -exec chown $user:$group {} \;
find $storage_dir   -type l -exec chown -h $user:$group {} \;
find $storage_dir ! -type d -exec chmod 660 {} \;
find $storage_dir   -type d -exec chmod 3770 {} \;

echo
echo "Setting PHPki storage directory to be owned by $user:$group and writing permissions... "
echo

# Display file list with new permissions
list_files=`ls -lahR ${storage_dir}`
#echo "$list_files"

echo "Checking if the required Apache modules are loaded..."

# Try to find apachectl
apachectl_list=("apachectl" "apache2ctl" "httpd" "/usr/sbin/apachectl" "/usr/sbin/apache2ctl" "/usr/sbin/httpd")
showmods=""
for i in "${apachectl_list[@]}"; do
	if [[ $(which ${apachectl_list[$i]} 2>/dev/null) ]]; then
    	showmods="${apachectl_list[$i]} -M"
    	break
    fi
done

# Get Bash version
bash_ver=$(echo $BASH_VERSION | head -c 1)

if [[ "$bash_ver" == 4 ]]; then
	# Associative array of Apache modules and comments
	# This only works for Bash 4
	declare -A mod_list
	mod_list=(["authz_core_module"]=""
			  ["auth_basic_module"]="(required for user validation)"
			  ["authz_groupfile_module"]="(required for user group authentication)"
			  ["authz_host_module"]="(required for user authentication)"
			  ["ssl_module"]="(please enable it and set up SSL)"
			 )
	if [[ $($showmods) ]]; then
		for i in "${!mod_list[@]}";	do
			if [[ ! $(${showmods} | grep $i ) ]]; then
				echo "Error: $i is not loaded ${mod_list[i]}"
			else echo "$i is loaded"
			fi
		done	
	else echo "Can't find apachectl or equivalent. Please ensure the following modules are enabled:" 
		for i in "${!mod_list[@]}";	do
			echo "- $i ${mod_list[$i]}"
		done
		exit $?
	fi
else
	# Python hack to emulate an associative array of Apache modules and comments
	# For Bash 3
	mod_list=("authz_core_module:"
        	"auth_basic_module:(required for user validation)"
        	"authz_groupfile_module:(required for user group authentication)"
        	"authz_host_module:(required for user authentication)"
        	"ssl_module:(please enable it and set up SSL)" )

	if [[ $($showmods) ]]; then
		for entry in "${mod_list[@]}" ; do
		    KEY=${entry%%:*}
		    VALUE=${entry#*:}
		    if [[ ! $(${showmods} | grep $KEY ) ]]; then
		    	printf "Error: %s is not loaded %s\n" "$KEY" "$VALUE"
		    else printf "%s is loaded\n" $KEY
		    fi
		done
	else
	    echo "Can't check for Apache modules. Please ensure the following modules are enabled:" 
	    for entry in "${mod_list[@]}" ; do
		    KEY=${entry%%:*}
		    VALUE=${entry#*:}
		    if [[ ! $(${showmods} | grep $KEY ) ]]; then
		    	printf "- %s %s\n" "$KEY" "$VALUE"
		    else printf "Apache module %s is loaded\n" $KEY
		    fi
		done
		exit $?
	fi
fi

echo "Please manually enable any missing Apache modules."
echo
echo "Done."