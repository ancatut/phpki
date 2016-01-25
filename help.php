<?php
include ('./config.php');
include ('./include/my_functions.php');
include ('./include/common.php');

printHeader('about');
?>

<div style="text-align:center"><br><br>
<table class="menu" style="width:70%">
<tr><th class="menu"><h2>PHPki HELP FILES</h2></th>
<tr><td class="menu" style="padding: 1em;">
<h3><a href="<?php print $config['base_url'] ?>help/PKI_basics.html">
	&raquo; PKI and E-mail Encryption - A Brief Explanation</a></h3>
<h3><a href="<?php print $config['base_url'] ?>help/cacert_install_ie.html">&raquo; Installing
	Our Root Certificate For Use With Outlook and Outlook Express</a></h3>
<h3><a href="<?php print $config['base_url'] ?>help/usercert_install_ie.html">&raquo; Installing
	Your Personal E-mail Certificate For Use With Outlook and Outlook
	Express</a></h3>
<h3><a href="<?php print $config['base_url'] ?>help/glossary.html">&raquo; Glossary</a></h3>
</td></tr>
</table>
<br><br>
</div>

<?php
printFooter();
?>
