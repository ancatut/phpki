<?php 
include('../config.php');
include(STORE_DIR.'/config/config.php');
include('../include/my_functions.php');

	$openvpn_client_cnf = gpvar('txtarea');
	$fd = fopen($config['openvpn_client_cnf_dir'].'/general_client.conf', 'wb');
	fwrite($fd, $openvpn_client_cnf);
	fclose($fd);
?>