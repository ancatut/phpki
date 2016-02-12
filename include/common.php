<?php

session_start();
umask(0007);

if (isset($_SERVER['PHP_AUTH_USER'])) {
	$PHPki_user = md5($_SERVER['PHP_AUTH_USER']);
}
else {
	$PHPki_user = md5('default');
}

$PHP_self = $_SERVER['PHP_SELF'];

setlocale(LC_CTYPE, "en_US.UTF-8", "en_US.UTF8", "en_GB.UTF-8", "en_GB.UTF8");

// Save & show username if a user is logged in 
if (isset($_SERVER['PHP_AUTH_USER'])) {
	$_SESSION['loggedin'] = true;
    $_SESSION['username'] = $_SERVER['PHP_AUTH_USER'];	
}

function printHeader($withmenu="default") {
	global $config;
	$title = (isset($config['header_title']) ? $config['header_title'] : 'PHPki Certificate Authority');

	$logout = gpvar('logout');
	$submit = gpvar('submit');

	switch ($withmenu) {
	case 'public':
	case 'about':	
		$style_css = 'css/style.css';
		$favicon   = 'images/favicon.ico';
		break;
	case 'ca':
	case 'admin':
	case 'setup':
	default:
		$style_css = '../css/style.css';
		$favicon   = '../images/favicon.ico';
		break;
	}

	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Expires: -1");
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
	header('Content-type: text/html; charset=utf-8');

?>
	<html>
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>PHPki: <?php echo $title ?> </title>
	<link rel="stylesheet" type="text/css" href="<?php echo $style_css ?>">
	<link rel="shortcut icon" href="<?php echo $favicon ?>">
	</head>
	<body>
	<div class="wrapper">
	
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
	<div class="header">
	<div class="<?php echo $logoclass ?>">PHPki</div>
	
	<div class="<?php echo $titleclass ?>"><?php echo $title ?></div>
	<?php 
	if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) {
 		print "<div class='logged-in'><i>Logged in as ". htvar($_SESSION['username']) . "</i></div>";
 	} 	

	switch ($withmenu) {
	case false:
	case 'about':
		print "<div class=".$menuclass.">";
		print "<a href='index.php'><button class='btn'>Public Menu</button></a>";
		print "<a href='ca/'><button class='btn'>Manage</button></a>";
		print "<a href='admin/setup.php'><button class='btn'>CA Setup</button></a>";
		print "<a href='help.php'><button class='btn'>Help</button></a>";
		print "<a href='about.php'><button class='btn'>About</button></a>";
		if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) {
			?>
			<form id="logout_btn" method="post" style="display:inline" action="">
			<input class='btn' name="logout" type="submit" style="background: #FF8566" value="Logout">
			</form>
			<?php 			
		}
		print "</div>";
		break;
	case 'setup':
		print "<div class=".$menuclass.">";
		print "<a href='../readme.php'><button class='btn' style='background: #FF8566'>View ReadMe</button></a>";
		print "<a href='../admin/setup.php'><button class='btn'>CA Setup</button></a>";
		print "<a href='../ca/'><button class='btn'>Manage</button></a>";
		print "<a href='../about.php'><button class='btn'>About</button></a>";
		print "<a href='../ca/help.php'><button class='btn'>CA Help</button></a>";
		if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) {
			?>
			<form id="logout_btn" method="post" style="display:inline" action="">
			<input class='btn' name="logout" type="submit" style="background: #FF8566" value="Logout">
			</form>
			<?php 			
		}
		print "</div>";
		break;
	case 'public':
		print "<div class=".$menuclass.">";

		print "<a href='index.php'><button class='btn'>Public Menu</button></a>";
		print "<a href='ca/'><button class='btn'>Manage</button></a>";

		if (file_exists('policy.html')) {
			print "<a style='color: red' href='policy.html' target='help'><button class='btn'>Policy</button></a>";
		}
			
		print "<a href='help.php'><button class='btn'>Help</button></a>";
		print "<a href='about.php'><button class='btn'>About</button></a>";
		
		if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) {
			?>
			<form id="logout_btn" method="post" style="display:inline" action="">
			<input class='btn' name="logout" type="submit" style="background: #FF8566" value="Logout">
			</form>
			<?php 			
		}
		print "</div>";
		break;
	
	case 'admin':
		print "<div class=".$menuclass.">";
		
		print "<a href='../index.php'><button class='btn'>Public Menu</button></a>";
		print "<a href='../ca/index.php'><button class='btn'>Manage CA</button></a>";
		if (! DEMO)  {
			print "<a href='setup.php'><button class='btn'>CA Setup</button></a>";
		}
			print "<a href='../openvpn/change_openvpn_settings.php'><button class='btn'>Edit OpenVPN Config</button></a>";
			print "<a href='../admin/index.php'><button class='btn'>Admin Panel</button></a>";
					
			if (file_exists('../policy.html')) {
				print "<a style='color: red' href='../policy.html'><button class='btn'>Policy</button></a>";
			}
	
			print "<a href='../ca/help.php'><button class='btn'>CA Help</button></a>";
			print "<a href='../about.php'><button class='btn'>About</button></a>";
			if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) {
			?>
			<form id="logout_btn" method="post" style="display:inline" action="">
			<input class='btn' name="logout" type="submit" style="background: #FF8566" value="Logout">
			</form>
			<?php 			
		}
		print "</div>";
		break;
	case 'ca':
	default:
		print "<div class=".$menuclass.">";

		print "<a href='../index.php'><button class='btn'>Public Menu</button></a>";
		print "<a href='../ca/index.php'><button class='btn'>Manage CA</button></a>";
		if (! DEMO)  {
			print "<a href='../admin/setup.php'><button class='btn'>CA Setup</button></a>";
		}
		print '<a href="../openvpn/change_openvpn_settings.php"><button class="btn">Edit OpenVPN Config</button></a>';
		print '<a href="../admin/index.php"><button class="btn">Admin Panel</button></a>';
		
		if (file_exists('../policy.html')) {
			print "<a style='color: red' href='../policy.html'><button class='btn'>Policy</button></a>";
		}
		
		print "<a href='../ca/help.php'><button class='btn'>CA Help</button></a>";
		print "<a href='../about.php'><button class='btn'>About</button></a>";
		
		if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) {
			?>
			<form id="logout_btn" method="post" style="display:inline" action="">
			<input class='btn' name="logout" type="submit" style="background: #FF8566" value="Logout">
			</form>
			<?php 			
		}
		print "</div>";
	}
	?>
	<hr width="100%" align="left" color="#99caff">
	</div>
	<div class="content">
	<?php
}

function printFooter() {
	?>
	
	</div>
	<div class="footer">
	<hr align="center" color="#99caff">
	<p style='margin-top: -5px; font-size: 8pt; text-align: center'>Based on PHPki <a href="http://sourceforge.net/projects/phpki/">v<?=PHPKI_VERSION?></a> - Copyright 2003 - William E. Roadcap</p>
	<p style='margin-top: -5px; font-size: 8pt; text-align: center'>Current version of update branch on GitHub: <a href="https://github.com/interiorcodealligator/phpki/releases/tag/v0.25.1">v0.25.1</a></p>
	
	</div>
	</div>
	
	</body>
	</html>
	<?php
}

?>
<script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>

<script>

/**
 * Implementation of logout for HTTP Basic Auth
 *
 */
 $(document).ready(function() {
		$('#logout_btn').submit(function() { // catch the form's submit event
		    var request = $.ajax({ // create an AJAX call...
			    // This creates a POST Basic Auth call to a PHP file.
			    // The call attempts to log in an user with false credentials,  
			    // and when that fails the previous user is logged out.
			    // Therefore, this acts like logging out a user previously logged in with Basic Auth.
		        type: "POST", // GET or POST
	            async: false,
	            beforeSend: function(xhr) {
	                xhr.setRequestHeader('WWW-Authenticate', 'Restricted Access');
	            },
	            username: "logmeout",
	            password: "12345",
	            headers: { "Authorization": "Basic " + btoa("logmeout" + ":" + "12345") },
		        url: "ca/index.php", // the file to call with the false credentials; this
		        					 // needs to point inside a folder protected by htaccess,
				 					 // even if the path points to nonsense.      
		        });
	        
	        // jQuery way I came up with to get the root folder of the PHPki website.
		    var pageURL = window.location.host + "/" + window.location.pathname;
			var pathArray = pageURL.split("/");
			var foldersArray = ["ca", "openvpn", "admin", "help", "about"];
			if (jQuery.inArray(pathArray[pathArray.length - 2], foldersArray) == -1) {
				pathArray.pop();
			}
			else if (jQuery.inArray(pathArray[pathArray.length - 1], foldersArray) == -1) {
				pathArray.pop();
				pathArray.pop();
			}			
			function isEmpty(value) {
				return value !== "";
			}
			pathArray = pathArray.filter(isEmpty);			
			var websiteRoot = pathArray.join("/") + "/";

			request.fail(function( jqXHR, textStatus ) {	
				// request.fail indicates we got a 401 header, which actually 
				// means successful logout, because Apache denied access.	
				alert("You have been successfully logged out.");
			});
		    request.success(function() {		    	
		    	alert("There was an error while attempting to log you out, please try again."); // We have a problem
			});						

			// Redirect user to logout page which redirects to public page
	    	location.assign(window.location.protocol + "//" + websiteRoot + 'logout.php'); 
	    	
		    return false; // cancel original event to prevent form submitting
		});
	});
	</script>