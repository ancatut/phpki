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
    \"cert-manager\" if the user can create and manage certs, but can't create/delete users under the admin panel and can't run PHPki setup,
#   [\"regular-user\"] for users who can only manage the certificates they have created themselves and can't access the admin panel or edit OpenVPN settings. `echo $'\n> '`" user_group
    user_group=${user_group:-"cert-manager"}
    
    echo "Creating the user account for $user_id..."
    htpasswd -c -m "$passwd_file" "$user_id" || exit

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
   # elif [[ ${user_group} == "admin" ]]; then
   #     temp=`cat $groups_file | grep cert-manager:`
   #     if [[ ${temp} == "" ]]; then
   #         echo "cert-manager: $user_id" >> $groups_file
   #     else 
   #         temp=`cat $groups_file | grep -E "cert-manager:.*${user_id}\s"`       
   #        if [[ ${temp} == "" ]]; then
   #             sed -i "/^cert-manager:/ s/$/ ${user_id}/" $groups_file
   #         fi
   #     fi
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

    htpasswd -m "$passwd_file" 'pkiadmin' || exit
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
echo -n "Enter the IP(s) or subnet address which will be allowed access to the user admin module in under ./admin [192.168.0.0/16]: " ; read -r y

echo -n "If you'd like to allow access to the other private folders based on IP or subnet, please enter the permitted address(es); otherwise leave empty: `echo $'\n> '`" ; read -r w

user=${x:-"www-data"}
group=${z:-"www-data"}
subnet_admin=${y:-'192.168.0.0/16'}
subnet_admin="${subnet_admin} 127.0.0.1"
subnet_general=${w:-''}

echo "Setting read-write permissions for $group over $passwd_file and $groups_file..."
chown $owner:$group $passwd_file $groups_file
chmod 760 $passwd_file $groups_file

echo
echo "Writing htaccess files..."

for i in ./include; do
	echo "Require all denied" >$i/.htaccess
done 

cat <<EOS > ./ca/.htaccess
SSLRequireSSL
AuthName "Restricted Area"
AuthType Basic
AuthUserFile "$passwd_file"
AuthGroupFile "$groups_file"
Require group admin cert-manager

EOS

cat <<EOS > ./admin/.htaccess 
SSLRequireSSL
AuthName "Restricted Area"
AuthType Basic
AuthUserFile "$passwd_file"
AuthGroupFile "$groups_file"
Require ip $subnet_admin
Require group admin

EOS

cat <<EOS > ./openvpn/.htaccess
SSLRequireSSL
AuthName "Restricted Area"
AuthType Basic
AuthUserFile "$passwd_file"
AuthGroupFile "$groups_file"
Require group admin cert-manager

EOS

if [[ "$subnet_general" != "" ]]; then
    echo "Require ip ${subnet_general}" >> ./ca/.htaccess
    echo "Require ip ${subnet_general}" >> ./openvpn/.htaccess
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
#if [[ $another_user ]]; then
#    echo "Other members of ${group} group except $user: $another_user."
#fi
echo
echo "Only the apache user will receive (read-write) permissions over the storage folder."
# Directories have sticky bits set.
find $storage_dir           -exec chown $user:$group {} \;
find $storage_dir   -type l -exec chown -h $user:$group {} \;
find $storage_dir ! -type d -exec chmod 600 {} \;
find $storage_dir   -type d -exec chmod 3700 {} \;

echo
echo "Writing permissions to PHPki storage directory..."
echo

# Display file list with new permissions
list_files=`ls -lahR ${storage_dir}`
#echo "$list_files"

echo "Enabling Apache's authz_groupfile module..."
a2enmod authz_groupfile
echo "Restarting Apache..."
service apache2 restart
echo
echo "All done."
