<?php
include ('./config.php');
include ('./include/my_functions.php');
include ('./include/common.php');

printHeader('about');
?>

<div style="text-align:center">
<table class="menu" style="width:100%">
<tr><th class="menu" style="font-size: 26px;">PHPki HELP FILES</th>
<tr><td class="menu" style="padding: 1em;">
<h3><a href="<?php print $config['base_url'] ?>help/PKI_basics.html">
	PKI and E-mail Encryption - A Brief Explanation</a></h3>
<h3><a href="<?php print $config['base_url'] ?>help/cacert_install_ie.html">Installing
	Our Root Certificate For Use With Outlook and Outlook Express</a></h3>
<h3><a href="<?php print $config['base_url'] ?>help/usercert_install_ie.html">Installing
	Your Personal E-mail Certificate For Use With Outlook and Outlook
	Express</a></h3>
<h3><a href="<?php print $config['base_url'] ?>help/glossary.html">Glossary</a></h3>
</td></tr>
</table>
</div>

<?php
printFooter();
?>
