<?php

umask(0007);

# session_start();

#if (! isset($_SERVER['PHP_AUTH_USER']) && ! isset($_SERVER['PHP_AUTH_PW'])) {
#	header('WWW-Authenticate: Basic realm="Restricted Access"');
#	header('HTTP/1.0 401 Unauthorized');
#}

if (isset($_SERVER['PHP_AUTH_USER'])) {
	$PHPki_user = md5($_SERVER['PHP_AUTH_USER']);
}

else {
	$PHPki_user = md5('default');
}

$PHP_SELF = $_SERVER['PHP_SELF'];

##### echo "<p>You entered {$_SERVER['PHP_AUTH_PW']} as your password.</p>"; <--

function printHeader($withmenu="default") {
	global $config;
	$title = (isset($config['header_title']) ? $config['header_title'] : 'PHPki Certificate Authority');

	$logout = gpvar('logout');
	$submit = gpvar('submit');
	
//	if ($logout == "Logout" || $submit == "Logout") {
		//$_SERVER['PHP_AUTH_USER']  = "";
//		header('HTTP/1.1 401 Unauthorized');
//	header('WWW-Authenticate: Basic realm="Restricted Access"');
		//header('Location: ../index.php');
		
//		exit;
//	}
	switch ($withmenu) {
	case 'public':
	case 'about':	
		$style_css = 'css/style.css';
		break;
	case 'ca':
	case 'admin':
	case 'setup':
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
		print "<div class=".$menuclass.">
		<a href='help.php'><button class='btn'>Help</button></a>
		<a href='about.php'><button class='btn'>About</button></a>";
		break;
	case 'setup':
	?>
		<div class="<?php echo $menuclass?>">
			<a href="../readme.php"><button class="btn">ReadMe</button></a>
			<a href="../admin/setup.php"><button class="btn">Setup</button></a>
			<a href="../about.php"><button class="btn">About</button></a>
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
	
	case 'admin':
		
		print "<div class=".$menuclass.">";
		
		if (DEMO)  {
			print "<a href='../index.php'><button class='btn'>Public</button></a>";
			print "<a href='../ca/index.php'><button class='btn'>Manage CA</button></a>";
		}
		else {
			print "<a href='setup.php'><button class='btn'>Re-run CA Setup</button></a>";
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
				
				<span style="display:inline">
				<form id="logout_btn" method="post" style="display:inline" action="">
				<input class='btn' name="logout" type="submit" style="background: #FF8566" value="Logout" onclick="logoutUser();">
				<!--  <button class='btn' name="logout" type="submit" style="background: #FF8566" >Log Out</button>-->
				</form>
				</span>
		</div>
	<?php 
	break;
	case 'ca':
	default:
		print "<div class=".$menuclass.">";

		if (DEMO)  {
			print "<a href='../index.php'><button class='btn'>Public</button></a>";
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
		
		<span style="display:inline">
		<form id="logout_btn" method="post" style="display:inline" action="">
		<input class='btn' name="logout" type="submit" style="background: #FF8566" value="Logout" onclick="logoutUser();">
		<!--  <button class='btn' name="logout" type="submit" style="background: #FF8566" >Log Out</button>-->
		</form>
		</span>
		
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
	<p style='margin-top: -5px; font-size: 8pt; text-align: center'>Current version of update branch on GitHub: <a href="https://github.com/interiorcodealligator/phpki/releases/tag/v0.16">v0.16</a></p>
	</body>
	</html>
	<?php
}

?>
<script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>

<script>

/*
 function logoutUser() {
	  setTimeout('location.reload(true)', 1000);
	  xmlhttp = GetXmlHttpObject();
	  if (xmlhttp==null) {
	  return;
	   }
	  //alert(xmlhttp);
	  var url = "logout.php";

	  xmlhttp.open("GET", url, true, "dummy_user", "dummy_password");

	  xmlhttp.setRequestHeader( "If-Modified-Since", "Sat, 1 Jan 2000 00:00:00 GMT" );
	  xmlhttp.setRequestHeader( 'Accept', 'message/x-formresult' );   
	  xmlhttp.setRequestHeader( "HTTP/1.1", "401 Unauthorized" );
	  xmlhttp.send(null);
	  xhttp.onreadystatechange = function() {
		  if (xhttp.readyState == 4 && xhttp.status == 200) {
			  location.assign("../index.php"); 
		  }
		};
	  }
function GetXmlHttpObject()
	   {
	  if (window.XMLHttpRequest)
	   {
	   // code for IE7+, Firefox, Chrome, Opera, Safari
	   return new XMLHttpRequest();
	   }
	   if (window.ActiveXObject)
	  {
	   // code for IE6, IE5
	   return new ActiveXObject("Microsoft.XMLHTTP");
	    }
	   return null;
	    }
	    */

/**
 * Implementation of logout for HTTP Basic Auth
  
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
	        url: "../ca/logout.php", // the file to call with false credentials        
	        });
	    request.done(function() {	  
	        alert("Logging out error, try again."); // Alert success	       
		});
		request.fail(function( jqXHR, textStatus ) {		
			// Failure indicates we got a 401 header, which actually means successful logout.	
			alert("You have been successfully logged out.");  
		});
		location.assign("../index.php"); // Redirect user to public page
	    return false; // cancel original event to prevent form submitting
	});
});



</script>