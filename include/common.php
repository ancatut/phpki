<?php

umask(0007);

if (isset($_SERVER['PHP_AUTH_USER'])) {
	$PHPki_user = md5($_SERVER['PHP_AUTH_USER']);
}
else {
	$PHPki_user = md5('default');
}

$PHP_SELF = $_SERVER['PHP_SELF'];


function printHeader($withmenu="default") {
	global $config;
	$title = (isset($config['header_title']) ? $config['header_title'] : 'PHPki Certificate Authority');
	switch ($withmenu) {
	case 'public':
	case 'about':
	case 'setup':
		$style_css = './css/style.css';
		break;
	case 'ca':
	case 'admin':
	default:
		$style_css = '../css/style.css';
		break;
	}

	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Expires: -1");
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");

?>
	<html>
	<head>
	<title>PHPki: <?php echo $title ?> </title>
	<link rel="stylesheet" type="text/css" href="<?php echo $style_css ?>">
	</head>
	<body>
	<?php

	if (isKonq()) { 
		$logoclass  = 'logo-konq';
		$titleclass = 'title-konq';
		$menuclass  = 'headermenu-konq';
	}
	else {
		$logoclass  = 'logo-ie';
		$titleclass = 'title-ie';
		$menuclass  = 'headermenu-ie';
	}

	?>
	<div class="<?php echo $logoclass ?>">PHPki</div>
	<div class="<?php echo $titleclass ?>"><?php echo $title ?></div>
	<?php

	switch ($withmenu) {
	case false:
	case 'about':
		break;
	case 'setup':
	?>
		<div class="<?php echo $menuclass?>">
			<a href="readme.php"><button class="btn">ReadMe</button></a>
			<a href="setup.php"><button class="btn">Setup</button></a>
			<a href="about.php"><button class="btn">About</button></a>
		</div>
		<?php
		break;
	case 'public':
		print "<div class=".$menuclass.">";

		if (DEMO)  {
			print "<a href='index.php'><button class='btn'>Public</button></a>";
			print "<a href='ca/'><button class='btn'>Manage</button></a>";
		}
		else {
			print "<a href='index.php'><button class='btn'>Public Menu</button></a>";
			print "<a href='ca/index.php'><button class='btn'>Manage CA</button></a>";
		}

		if (file_exists('policy.html')) {
			print "<a style='color: red' href='policy.html' target='help'><button class='btn'>Policy</button></a>";
		}
		?>		
		<a href="help.php"><button class="btn">Help</button></a>
		<a href="about.php"><button class="btn">About</button></a>
		</div>
		<?php
		break;
	case 'ca':
	default:
		print "<div class=".$menuclass.">";

		if (DEMO)  {
			print "<a href='../index.php'>Public</a>";
			print "<a href='../ca/index.php'><button class='btn'>Manage CA</button></a>";
		}
		else {
			print "<a href='../ca/index.php'><button class='btn'>Manage CA</button></a>";
		}
		?>
		<a href="../openvpn/change_openvpn_settings.php"><button class="btn">Edit OpenVPN Config</button></a>
		<a href="../admin/index.php"><button class="btn">Admin Panel</button></a>
		<?php
		if (file_exists('../policy.html')) {
			print "<a style='color: red' href='../policy.html'><button class='btn'>Policy</button></a>";
		}
		?>

		<a href='../help.php'><button class="btn">Help</button></a>
		<a href='../about.php'><button class="btn">About</button></a>
		</div>
		<?php
	}
		?>
	<hr width="100%" align="left" color="#99caff">
	<?php
}

function printFooter() {
	?>
	<br>
	<hr width="99%" align="left" color="#99caff">
	<p style='margin-top: -5px; font-size: 8pt; text-align: center'>Based on PHPki <a href="http://sourceforge.net/projects/phpki/">v<?=PHPKI_VERSION?></a> - Copyright 2003 - William E. Roadcap</p>
	<p style='margin-top: -5px; font-size: 8pt; text-align: center'>Current version of update branch on GitHub: <a href="https://github.com/interiorcodealligator/phpki/releases/tag/v0.11">v0.11</a></p>
	</body>
	</html>
	<?php
}

?>
