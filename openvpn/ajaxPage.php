<?php 
include('../config.php');
include(STORE_DIR.'/config/config.php');
include('../include/my_functions.php');

	$openvpn_client_basecnf_text = gpvar('cnf_textarea');
	$fd = fopen($config['openvpn_client_cnf_dir'].'/client_basecnf.conf', 'wb');
	fwrite($fd, $openvpn_client_basecnf_text);
	fclose($fd);
?>