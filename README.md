== NOTICE == 
This application is designed to be an easy to use "certificate factory" requiring minimum human intervention to administer.  It is intended for use within a trusted INTRAnet for the creation and management of x.509 e-mail digital certificates by departmental managers.  IT IS NOT INTENDED FOR USE OVER THE INTERNET.

This application stores private keys within a sub-directory, making them potentially susceptible to compromise.  Extra care has been taken in the design of this application to protect the security of your certificates, on the condition that you INSTALL IT AS THE ROOT USER.  However, no software is 100% secure, AND NO PROMISES OR GUARANTEES ARE MADE!


== REQUIREMENTS == 
PHPki was developed and tested on Mandrake and RedHat GNU/Linux systems. It requires the Apache web server, PHP, and OpenSSL.  I do not as yet know if it is sensitive to particular versions of those resources.

Your web server must be configured with "AllowOverride All" effective in the directory where PHPki is installed.  If you don't know what this means, then go Googling.

The .htaccess file sets some default settings for compatibility with older versions of PHP but it's not guaranteed if this program will work well with PHP 5.3 or older.

With PHP 5 (< PHP 5.4), make sure register_long_arrays is turned on in php.ini or .htaccess. Otherwise, you may get an error similar to "method=post was not found on this server."

The updates to PHPki were done on PHP version 5.6 and Apache version 2.4.

FIRST INSTALLATION:
Make sure "AllowOverride All" is set in your Apache configuration file. This is necessary because PHPki uses .htaccess files to implement HTTP authentication and to enforce file access restrictions.  If you must change this Apache setting, don't forget to restart Apache.

Unpack the PHPki tarball onto your web server. For example:

	cp phpki.tar.gz /var/tmp
	cd /var/www/html
	tar -xzvf /var/tmp/phpki.tar.gz

Then run
	
	sudo make distclean
	
to clear out any files that may exist in the web folder from a previous installation (warning: this deletes any .htaccess files), set the root folder's .htaccess and set read-write permissions to run setup.

Install Composer by running from the installation folder (this is required for project dependencies):
	
	php composer.phar install

To configure the certificate authority and create your root certificate, point your browser to where you unpacked PHPki. For example:

	http://www.domain.com/phpki/admin/setup.php

Enter all the requested information into the web form and click the Submit button.  If all goes well, you should see a page telling you that your root certificate has been created. 

To access the PHPki public content menu, point your browser to your PHPki installation (i.e. http://www.domain.com/phpki/).  To access the PHPki certificate management menu, point your browser to the "ca"
directory under your PHPki installation (i.e. https://www.domain.com/phpki/ca/). You must use SSL (https://) to access the PHPki certificate management menu if you have secured the application using the secure.sh script.

== USING SPECIAL CHARACTERS ==
If you want to use UTF-8 characters in your input, please also make sure to your server has the en_US.UTF-8 or en_GB.UTF-8 locale installed. Otherwise, special characters will just be deleted, which will make re-entering passwords with special chars in them impossible. 
Therefore, check your installed locales by running
	
	locale -a
	
and install the UTF-8 locale if it's missing by running
	
	sudo locale-gen en_US.UTF-8
	sudo update-locale

and then restart your server.

== SECURITY & USERS ==
After finishing the setup, you must run 

	sudo bash secure.sh
	
in the PHPki directory to set more restrictive Unix file permissions, and to create the Apache .htaccess files which are necessary to force SSL access, HTTP authentication, and directory access restrictions. If you don't do this you will likely be extremely screwed! Don't say you weren't warned.

The secure.sh script will attempt to create a file for your user list and passwords.  If it fails in that attempt, you will have to use Apache's htpasswd utility to manually create a "phpkipasswd" file in the location you specified during setup.

	htpasswd -cm /var/www/phpkipasswd username

Normal users may only manage the certificates they create.  Administrators can manage all certificates.  The default administrator account is "pkiadmin".  The secure.sh script will attempt to add this user to your phpkipasswd file when it is first created.  Other users can be made administrators by updating their group via the SysAdmin Panel.

You may add additional users and change passwords using your browser after you have successfully installed PHPki and created your phpkipasswd file with at least one user. Point your browser to http://www.domain.com/phpki/admin/.  

== UPGRADING == 
Install and configure as if it were a first time installation (see above). Be sure to specify the same root certificate password and user password file location you used with the previous version. You can re-run the setup anytime from the "CA Setup" button on the menu.

From the old installation, copy all certificates, crls, and user defaults to the store directory specified during setup.

	rm -fr store_directory/CA
	cp -v --archive oldphpki/CA   store_directory
	cp -v --archive oldphpki/config/user-*.php  newphpki/ca/config

These upgrade instructions have not been thoroughly tested and may be incomplete.  Please be sure to fully backup your old PHPki installation before upgrading.


== CONTACT == 
Post all correspondence to the PHPki project page http://sourceforge.net/projects/phpki/

---END OF FILE---