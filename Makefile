VERSION = 0.83
UID = $(shell id -u)
GID = $(shell id -g)

all:
	@echo "Read the README file!!"
	
clean:
	find . -name .*.swp -follow -exec rm -f {} \;
	find . -name test.php  -exec rm -f {} \;
	find . -name phpinfo.php -exec rm -f {} \;
	find . -name "deleteme*" -exec rm -f {} \;
	find . -name "newcert*.???" -exec rm -f {} \;
	find . -name "privkey*.???" -exec rm -f {} \;
	find . -name "newreq*.???" -exec rm -f {} \;

distclean: clean

	find . -type d -follow -exec chmod 2777 {} \;

	#rm -f ca/index.php
	#echo '<?php\nheader("Location: ./../index.php");\n?>' > ca/index.php

	echo '<?php' > config.php
	echo 'define("PHPKI_VERSION", "$(VERSION)");' >> config.php
	echo 'define("DEMO", FALSE);' >> config.php
	echo 'define("STORE_DIR", "");' >> config.php
	echo 'define("BASE_URL", "");' >> config.php
	echo '?>' >> config.php

	rm -f index.php admin/setup.php
	#ln -sf readme.php index.php
	ln -sf setup.php-presetup admin/setup.php

	find . ! -type d -follow -exec chown $(UID).$(GID) {} \;
	find . ! -type d -follow -exec chmod 0666 {} \;
	find .   -type d -follow -exec chown $(UID).$(GID) {} \;
	find .   -type d -follow -exec chmod 2777 {} \;
	find .   -name .htaccess -follow -exec rm -f {} \;

	chmod 700 secure.sh

	echo 'Options FollowSymLinks' > .htaccess
	echo 'php_flag allow_url_fopen off' >> .htaccess	
	echo 'php_flag register_globals off' >> .htaccess
	echo 'php_flag register_long_arrays on' >> .htaccess
	echo 'php_flag magic_quotes_gpc off' >> .htaccess
	echo 'AddType application/x-x509-ca-cert .crt  .pem' >> .htaccess
	echo 'AddType application/pkix-crl    .crl' >> .htaccess
	echo 'AddType application/pkix-cert   .cer .der' >> .htaccess

	@echo "\n\n=================================================================="
	@echo "Point your browser to your PHPki installation to configure and"
	@echo "create your root certificate. (i.e. http://www.domain.com/phpki/)\n"

secure:
	@./secure.sh

fixperms:
	@./secure.sh
