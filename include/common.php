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
			<a class="<?php echo $menuclass ?>" href="readme.php">ReadMe</a>
			<a class="<?php echo $menuclass ?>" href="setup.php">Setup</a>
			<a class="<?php echo $menuclass ?>" href="about.php" target="_about">About</a>
		</div>
		<?php
		break;
	case 'public':
		print "<div class='".$menuclass."'>";

		if (DEMO)  {
			print "<a class='".$menuclass."' href='index.php'>Public</a>";
			print "<a class='".$menuclass."' href='ca/'>Manage</a>";
		}
		else {
			print "<a class='".$menuclass."' href='index.php'>Public Menu</a>";
			print "<a class='".$menuclass."' href='ca/index.php'>Manage CA</a>";
		}

		if (file_exists('policy.html')) {
			print "<a class='".$menuclass."' style='color: red' href='policy.html' target='help'>Policy</a>";
		}
		?>		
		<a class="<?php print $menuclass?>" href="help.php" target="_help">Help</a>
		<a class="<?php print $menuclass?>" href="about.php" target="_about">About</a>
		</div>
		<?php
		break;
	case 'ca':
	default:
		print "<div class='".$menuclass."'>";

		if (DEMO)  {
			print "<a class='".$menuclass."' href='../index.php'>Public</a>";
			print "<a class='".$menuclass."' href='../ca/index.php'>Manage</a>";
		}
		else {
			print "<a class='".$menuclass."' href='../ca/index.php'>Manage CA</a>";
		}
		?>
		<a class="<?php print $menuclass?>" href="../admin/index.php">Admin Panel</a>
		<?php
		if (file_exists('../policy.html')) {
			print "<a class='".$menuclass."' style='color: red' href='../policy.html' target='help'>Policy</a>";
		}
		?>

		<a class="<?php print $menuclass?>" href='../help.php' target='_help'>Help</a>
		<a class="<?php print $menuclass?>" href='../about.php' target='_about'>About</a>
		</div>
		<?php
	}
		?>
	<hr width="99%" align="left" color="#99caff">
	<?php
}


function printFooter() {
	?>
	<br>
	<hr width="99%" align="left" color="#99caff">
	<p style='margin-top: -5px; font-size: 8pt; text-align: center'>Based on PHPki <a href="http://sourceforge.net/projects/phpki/">v<?=PHPKI_VERSION?></a> - Copyright 2003 - William E. Roadcap</p>
	<p style='margin-top: -5px; font-size: 8pt; text-align: center'>Current version of update branch on GitHub: <a href="https://github.com/interiorcodealligator/phpki/releases/tag/v0.10.1">v0.10.1</a></p>
	</body>
	</html>
	<?php
}

?>
