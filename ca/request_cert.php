<?php
include('../config.php');
include(STORE_DIR.'/config/config.php');
include('../include/my_functions.php');
include('../include/common.php') ;
include('../include/openssl_functions.php') ;

# User's preferences file
$user_cnf = $config['home_dir']."/config/user-".strtr($PHPki_user,'/\\','|#').'.php';

$form_stage	  = gpvar('form_stage');
$submit		  = gpvar('submit');
$country 	  = gpvar('country');
$province	  = gpvar('province');
$locality	  = gpvar('locality');
$organization = gpvar('organization');
$unit 		  = gpvar('unit');
$common_name  = gpvar('common_name');
$email		  = gpvar('email');
$passwd		  = gpvar('passwd');
$passwdv	  = gpvar('passwdv');
$pwd_use	  = gpvar('pwd_use');
$expiry		  = gpvar('expiry');
$keysize	  = gpvar('keysize');	
$cert_type	  = gpvar('cert_type');

# To repopulate the fields in the form after error.
$hidden_fields = '
    <input type="hidden" name="country" value="' . htvar($country) . '">
    <input type="hidden" name="province" value="' . htvar($province) . '">
    <input type="hidden" name="locality" value="' . htvar($locality) . '">
    <input type="hidden" name="organization" value="' . htvar($organization) . '">
    <input type="hidden" name="unit" value="' . htvar($unit) . '">
    <input type="hidden" name="common_name" value="' . htvar($common_name) . '">
    <input type="hidden" name="email" value="' . htvar($email) . '">
    <input type="hidden" name="passwd" value="' . htvar($passwd) . '">
    <input type="hidden" name="passwdv" value="' . htvar($passwdv) . '">
    <input type="hidden" name="pwd_use" value="' . htvar($pwd_use) . '">
    <input type="hidden" name="expiry" value="' . htvar($expiry) . '">
    <input type="hidden" name="keysize" value="' . htvar($keysize) . '">
    <input type="hidden" name="cert_type" value="' . htvar($cert_type) . '">
';

switch ($form_stage):

case 'back_to_menu':
	header('Location: index.php');
	break;

case 'validate':
	$er = '';

	if (! $country)      $er .= 'Missing Country<br>';
	if (! $province)     $er .= 'Missing State/Province<br>';
	if (! $locality)     $er .= 'Missing Locality (City/County)<br>';
	if (! $organization) $er .= 'Missing Organization (Company/Agency)<br>';
	if (! $unit)         $er .= 'Missing Unit/Department<br>';
	if (! $common_name)  $er .= 'Missing User\'s Full Name<br>';
	if (! $email)        $er .= 'Missing E-mail Address<br>';

	if (! username_validchars($common_name))		$er .= "Username contains invalid characters<br>";
	
	if (($cert_type == 'email' || $cert_type == 'email_signing') && ! $passwd)       $er .= 'Missing Certificate Password<br>';
	if (($cert_type == 'email' || $cert_type == 'email_signing') && ! $passwdv)      $er .= 'Missing Certificate Password Verification "Again"<br>';

	if ( $passwd && strlen($passwd) < 8 )
		$er .= 'Certificate password is too short.<br>';

	if ( $passwd and $passwd != $passwdv )
		$er .= 'Password and password verification do not match.<br>';
	
	if ( $email && ! is_email($email) )
		$er .= 'E-mail address ('. htvar($email) . ') may be invalid.<br>';
	
	if ( $organization && (! name_validchars($organization)))
		$er .= 'Organization name is invalid.<br>';
	if ( $unit && (! name_validchars($unit)))
		$er .= 'Department name is invalid.<br>';
	if ( $locality && (! name_validchars($locality)))
		$er .= 'Locality name is invalid.<br>';
	if ( $province && (! name_validchars($province)))
		$er .= 'Province name is invalid.<br>';
	if ( $country && (!is_alpha($country))) # Check if country code only contains alphabetic characters
		$er .= 'Country code is invalid.<br>';

	if ( $er )
		$er = '<h2>ERROR(S) IN FORM:</h2><h4><blockquote>' . $er . '</blockquote></h4>';


	if ($email && ($serial = CAdb_has_valid($email,$common_name))) { 	
		$er = '';
		$certtext = CA_cert_text($serial);
		$er .= '<h2>A valid certificate already exists for ' . htvar("$common_name  <$email>") . '</h2>';
		$er .= '</font><blockquote><pre> ' . htvar($certtext) . ' </pre></blockquote>';
	}

	if ($er)  { 
		printHeader();
		?>
		
		<form action="<?php print htvar($PHP_SELF)?>" method="post">
		<input class="btn" type="submit" name="submit" value='Go Back'>
		<font color=#ff0000><?php print $er ?></font>
		<br><input class="btn" type="submit" name="submit" value='Go Back'>
		
		<?php
		print $hidden_fields;
		?>
		</form>
		<?php 
		printFooter();
		break;
	}

case 'confirm':
	printHeader();

	?>
	<h4>You are about to create a certificate using the following information:</h4>
	<table style="width:500px"><tr>
    	<td style='white-space: nowrap; width:25%'>
    	<p align="right">
    	User's Name<br>
    	E-mail Address<br>
    	Organization<br>
		Department/Unit<br>
		Locality<br>
		State/Province<br>
    	Country<br>
		Certificate Life<br>
		Password use<br>
		Key Size<br>
		Certificate Use<br>
    	</td>

    	<td>
    	<?php
		print htvar($common_name) . '<br>';
    	print htvar($email) . '<br>';
    	print htvar($organization) . '<br>';
    	print htvar($unit) . '<br>';
    	print htvar($locality) . '<br>';
    	print htvar($province) . '<br>';
    	print htvar($country) . '<br>';
    	switch($expiry) {
    		case 0.083:
    			print '1 Month<br>';
    			break;
    		case 0.25:
    			print '3 Months<br>';
    			break;
    		case 0.5:
    			print '6 Months<br>';
    			break;
    		case 1:
    			print '1 Year<br>';
    			break;
    		default: print htvar($expiry). ' Years<br>';
    	}
		print ($pwd_use == "both_pwd" ? "Encrypt both PKCS#12 file and private key" : "Encrypt only PKCS#12 file"). "<br>";
		print htvar($keysize). ' bits<br>';
		print htvar($cert_type). '<br>';
	?>
    	</td>
  	</tr></table>

	<h4>Are you sure?</h4>
	<p><form action="<?php print htvar($PHP_SELF)?>" method="post">
	<?php print $hidden_fields ?>
	<input type="hidden" name="form_stage" value="final">
  	<input class="btn" type="submit" name="submit" value='Yes! Create and Download'>
  	<input class="btn" type="submit" name="submit" value='Edit Details'>
  	<input class="btn" type="submit" name="submit" value='Go Back'>
	</form>

	<?php
	printFooter();

	# Save user's defaults 
	$fp = fopen($user_cnf,'w');
	$x = '<?php
	$country = \''.addslashes($country).'\';
	$locality = \''.addslashes($locality).'\';
	$province = \''.addslashes($province).'\';
	$organization = \''.addslashes($organization).'\';
	$unit = \''.addslashes($unit).'\';
	$expiry = \''.addslashes($expiry).'\';
	$keysize = \''.addslashes($keysize).'\';
	?>';
	fwrite($fp,$x);
	fclose($fp);

	break;

case 'final':
	if ($submit == "Yes! Create and Download") {
		if (! $serial = CAdb_has_valid($email, $common_name)) {
			list($ret,$errtxt) = CA_create_cert($cert_type, $country, $province, $locality, $organization, $unit, $common_name, $email, $expiry, $passwd, $pwd_use, $keysize);
			if (! $ret) {
	            printHeader();

				?>
				<form action="<?php print htvar($PHP_SELF)?>" method="post">
                		<h2 style="color:#ff0000">There was an error creating your certificate.</h2><br>
	                	<blockquote>
	                	<h3>Debug Info:</h3>
				<pre><?php print $errtxt ?></pre>
				</blockquote>
				<p>
				<?php print $hidden_fields?>
				<input class="btn" type="submit" name="submit" value="Back">
				<p>
				</form>
				<?php

				printFooter();
				break;
        		}
        		else {
					$serial = $errtxt;
					log_password_entry($config['passwd_log'], $common_name, $email, $passwd, $pwd_use);					
        		}
		}

                switch($cert_type) {
                case 'server':
                        upload(array($config['private_dir']."/$serial-key.pem",$config['new_certs_dir']."/$serial.pem", $config['cacert_pem']), "$common_name ($email).pem",'application/pkix-cert');
                        break;
                case 'email':
                case 'email_signing':
				case 'time_stamping':
                case 'vpn_client_server':
                case 'vpn_client':
                case 'vpn_server':
                        upload($config['pfx_dir']."/$serial.pfx", $common_name."_($email).p12", 'application/x-pkcs12');
                        break;
                }

		break;
	}	
	else if ($submit == "Cancel")
		header("Location: index.php");
default:
	if ($form_stage == 'clear') {
	#
	# All fields are reset.
	#
		$country = "";
		$province = "";
		$locality = "";
		$organization = "";
		$unit = "";
		$email = "";
		$password = "";
		$expiry = 1;
		$keysize = 2048;
		$cert_type = "email";
	}
	else {
	# 
	# Default fields to reasonable values if necessary.
	#
	if (! $submit and file_exists($user_cnf)) include($user_cnf);

	if (! $country)       $country = $config['country'];
	if (! $province)      $province = $config['province'];
	if (! $locality)      $locality = "";
	if (! $organization)  $organization = "";
	if (! $unit)          $unit = "";
	if (! $email)         $email = "";
	if (! $expiry)        $expiry = 1;
	if (! $keysize)       $keysize = 2048;
	if (! $cert_type)     $cert_type = 'email';
	}

	printHeader();
	?>
	
	<body onLoad="self.focus();document.request.common_name.focus()">
	
	<form action="<?php print htvar($PHP_SELF)?>" method="post" name="request">
	<div>
	<table style="width: 100%">
	<tr><th colspan="2"><h2>Certificate Request Form</h2></th></tr>

	<tr>
	<td width=30%>Common Name<br>(i.e. User real name or computer hostname) </td>
	<td><input class="inputbox" type="text" name="common_name" value="<?php print  htvar($common_name)?>" size="50" maxlength="60"></td>
	</tr>

	<tr>
	<td>E-mail Address </td>
	<td><input class="inputbox" type="text" name="email" value="<?php print htvar($email)?>" size="50" maxlength="60"></td>
	</tr>

	<tr>
	<td>Organization (Company/Agency)</td>
	<td><input class="inputbox" type="text" name="organization" value="<?php print htvar($organization)?>" size="50" maxlength="60"></td>
	</tr>

	<tr>
	<td>Department/Unit </td><td><input class="inputbox" type="text" name="unit" value="<?php print  htvar($unit) ?>" size="40" maxlength="60"></td>
	</tr>

	<tr>
	<td>Locality (City/County)</td><td><input class="inputbox" type="text" name="locality" value="<?php print  htvar($locality) ?>" size=30 maxlength=30></td>
	</tr>

	<tr>
	<td>State/Province</td><td><input class="inputbox" type="text" name="province" value="<?php print  htvar($province) ?>" size=30 maxlength=30></td>
	</tr>

	<tr>
	<td>Country</td>
	<td><input class="inputbox" type="text" name="country" value="<?php print  htvar($country) ?>" size=2 maxlength=2></td>
	</tr>

	<tr>
	<td>Certificate Password<br>
	(it should be min. 8 characters long and it cannot contain any single quotes)</td>
	
	<td>
	<input class="inputbox" type="password" name="passwd" value="<?php print htvar($passwd) ?>" size="30">&nbsp;&nbsp; 
	Re-type password: <input class="inputbox" type=password name=passwdv  value="<?php print  htvar($passwdv) ?>" size="30">
	<div class="picker">Use this password for: <br>
	<input type="radio" name="pwd_use" value="both_pwd" <?php ($pwd_use == "both_pwd" || !$pwd_use) ? print "checked" : ""?>> Both PKCS#12 file and private key encryption<br>
	<input type="radio" name="pwd_use" value="pkcs12_pwd" <?php ($pwd_use == "pkcs12_pwd") ? print "checked" : ""?>> Only PKCS#12 file encryption<br>
	</div>
	</td>

	</tr>

	<tr>
	<td>Certificate Life </td>
	<td><select class="inputbox" name="expiry">
	<?php

	# Fixed bug where certificate life would rever to 1 Month if < 1 Year
	print "<option value=0.083 " . ($expiry == 0.083 ? "selected='selected'" : "") . " >1 Month</option>\n" ;
	print "<option value=0.25 " . ($expiry == 0.25 ? "selected='selected'" : "") . " >3 Months</option>\n" ;
	print "<option value=0.5 " . ($expiry == 0.5 ? "selected='selected'" : "") . " >6 Months</option>\n" ;
	print "<option value=1 " . ($expiry == 1 ? "selected='selected'" : "") . " >1 Year</option>\n" ;
	for ( $i = 2 ; $i < 6 ; $i++ ) {
		print "<option value=$i " . ($expiry == $i ? "selected='selected'" : "") . " >$i Years</option>\n" ;
	}

	?>

	</select></td>
	</tr>

	<tr>
	<td>Key Size </td>
	<td><select class="inputbox" name="keysize">
	<?php
	for ( $i = 512 ; $i < 4096 ; $i+= 512 ) {
		print "<option value=$i " . ($keysize == $i ? "selected='selected'" : "") . " >$i bits</option>\n" ;
	}

	?>
	</select></td>
	</tr>

	<tr>
	<td>Certificate Use: </td>
	<td><select class="inputbox" name="cert_type">
	<?php
	print "<option value='email' ".($cert_type=="email"?"selected":"").">E-mail, SSL Client</option>";
	print "<option value='email_signing' ".($cert_type=="email_signing"?"selected":"").">E-mail, SSL Client, Code Signing</option>";
	print "<option value='server' ".($cert_type=="server"?"selected":"").">SSL Server</option>";
	print "<option value='vpn_client' ".($cert_type=="vpn_client"?"selected":"").">VPN Client Only</option>";
	print "<option value='vpn_server' ".($cert_type=='vpn_server'?'selected':'').">VPN Server Only</option>";
	print "<option value='vpn_client_server' ".($cert_type=="vpn_client_server"?"selected":"").">VPN Client, VPN Server</option>";
	print "<option value='time_stamping' ".($cert_type=="time_stamping"?"selected":"").">Time Stamping</option>";
	?>
	</select></td>
	</tr>
	<tr>
	
	<td style="color: red">* All fields are required</td>
	<td>
	<div style="text-align:left">
	<input class="btn" type="submit" style="display: inline;" name="submit" value="Submit Request">
	<input type="hidden" name="form_stage" value="validate">
	
	
	</form>
	
	<form action="<?php print htvar($PHP_SELF)?>" method="post" name="request2" style="display: inline-block" >
	<div>
	<input class="btn" type="submit" name="submit2" value="Clear All">
	<input type="hidden" name="form_stage" value="clear">
	</div>
	</form>
	<form action="index.php" method="post" name="request3" style="display: inline-block" >
	<div>
	<input class="btn" type="submit" name="submit3" value="Go Back">
	<input type="hidden" name="form_stage" value="back_to_menu">
	</div>
	</form>
	</div>
	</div>
	</td>
	</tr>
	</table>
	<?php

	printFooter();
endswitch;

?>
