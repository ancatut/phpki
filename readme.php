<?php

include('./config.php');
include('./include/my_functions.php');
include('./include/common.php');

printHeader('about');
print '<div style="text-align:center"><font color="red"><h1>READ ME</h1></font></div>';
print '<div style="text-align:left; width:80%"><pre>';
readfile('./README.md');
print '</pre></div>';
printFooter();
?>
