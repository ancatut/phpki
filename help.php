<?php
include ('./config.php');
include ('./include/my_functions.php');
include ('./include/common.php');

printHeader('about');
?>
<div style="text-align:center">
	<h1>PHPki HELP FILES</h1>
	<h3><a href="<?php print $config['base_url'] ?>help/PKI_basics.html">PKI and
			E-mail Encryption - A Brief Explanation</a></h3>
	<h3><a href="<?php print $config['base_url'] ?>help/cacert_install_ie.html">Installing
			Our Root Certificate For Use With Outlook and Outlook Express</a></h3>
	<h3><a href="<?php print $config['base_url'] ?>help/usercert_install_ie.html">Installing
				Your Personal E-mail Certificate For Use With Outlook and Outlook
				Express</a></h3>
	<h3><a href="<?php print $config['base_url'] ?>help/glossary.html">Glossary</a></h3>
</div>
<?php
printFooter();
?>
