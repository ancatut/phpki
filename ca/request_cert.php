<?php

include('../config.php');
include(STORE_DIR.'/config/config.php');
include('../include/my_functions.php');
include('../include/common.php') ;
include('../include/openssl_functions.php') ;

# User's preferences file
$user_cnf = $config['home_dir']."/config/user-".strtr($PHPki_user,'/\\','|#').'.php';

# Retrieve GET/POST values
// $form_stage	= gpvar('form_stage'); 
// $submit = ""; 
// $country = "";
// $province = "";
// $locality = "";
// $organization = "";
// $unit = "";
// $common_name = "";
// $email = "";
// $passwd = "";
// $passwdv = "";
// $expiry = "";
// $keysize = "";
// $cert_type = "";
#if (isset($_GET['form_stage']) || isset($_POST['form_stage']))
	$form_stage	= gpvar('form_stage');
#if (isset($_GET['submit']) || isset($_POST['submit']))	
	$submit	= gpvar('submit');
#if (isset($_GET['country']) || isset($_POST['country']))	
	$country = gpvar('country');
#if (isset($_GET['province']) || isset($_POST['province']))	
	$province	= gpvar('province');
#if (isset($_GET['locality']) || isset($_POST['locality']))	
	$locality	= gpvar('locality');
#if (isset($_GET['organization']) || isset($_POST['organization']))	
	$organization	= gpvar('organization');
#if (isset($_GET['unit']) || isset($_POST['unit']))	
	$unit = gpvar('unit');
#if (isset($_GET['common_name']) || isset($_POST['common_name']))	
	$common_name	= gpvar('common_name');
#if (isset($_GET['email']) || isset($_POST['email']))	
	$email	= gpvar('email');
#if (isset($_GET['passwd']) || isset($_POST['passwd']))	
	$passwd	= gpvar('passwd');
#if (isset($_GET['passwdv']) || isset($_POST['passwdv']))	
	$passwdv	= gpvar('passwdv');
#if (isset($_GET['expiry']) || isset($_POST['expiry']))	
	$expiry	= gpvar('expiry');
#if (isset($_GET['keysize']) || isset($_POST['keysize']))	
	$keysize	= gpvar('keysize');
#if (isset($_GET['cert_type']) || isset($_POST['cert_type']))	
	$cert_type	= gpvar('cert_type');


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

	if (($cert_type == 'email' || $cert_type == 'email_signing') && ! $passwd)       $er .= 'Missing Certificate Password<br>';
	if (($cert_type == 'email' || $cert_type == 'email_signing') && ! $passwdv)      $er .= 'Missing Certificate Password Verification "Again"<br>';

	if ( $passwd && strlen($passwd) < 8 )
		$er .= 'Certificate password is too short.<br>';

	if ( $passwd and $passwd != $passwdv )
		$er .= 'Password and password verification do not match.<br>';

	//if ( ! is_alnum($passwd) or ! is_alnum($passwdv) )
	//	$er .= 'Password contains invalid characters.<br>';

	if ( $email && ! is_email($email) )
		$er .= 'E-mail address ('. htvar($email) . ') may be invalid.<br>';
	
	if ( $country && (!is_alpha($country))) # Check if country code only contains alphabetic characters
		$er .= 'Country code is invalid.';

	if ( $er )
		$er = '<h2>ERROR(S) IN FORM:</h2><h4><blockquote>' . $er . '</blockquote></h4>';


	if ($email && ($serial = CAdb_in($email,$common_name))) { 	
		$er = '';
		$certtext = CA_cert_text($serial);
		$er .= '<h2>A valid certificate already exists for ' . htvar("$common_name  <$email>") . '</h2>';
		$er .= '</font><blockquote><pre> ' . htvar($certtext) . ' </pre></blockquote>';

	}

	if ($er)  { 
		printHeader();
		?>
		
		<form action="<?php print $PHP_SELF ?>" method="post">
		<input type="submit" name="submit" value='Go Back'>
		<font color=#ff0000><?php print $er ?></font>
		<br><input type="submit" name="submit" value='Go Back'>
		
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
	
		print htvar($keysize). ' bits<br>';
		print htvar($cert_type). '<br>';
	/* print htvar($expiry). ' Year'.($expiry == 1 ? '' : 's').'<br>'; */
	?>
    	</td>

  	</tr></table>

	<h4>Are you sure?</h4>
	<p><form action="<?php print $PHP_SELF?>" method="post">
	<?php print $hidden_fields ?>
	<input type="hidden" name="form_stage" value="final">
  	<input type="submit" name="submit" value='Yes! Create and Download' >&nbsp;
  	<input type="submit" name="submit" value='Go Back'>
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
		if (! $serial = CAdb_in($email, $common_name)) {
			list($ret,$errtxt) = CA_create_cert($cert_type, $country, $province, $locality, $organization, $unit, $common_name, $email, $expiry, $passwd, $keysize);

			if (! $ret) {
	                	printHeader();

				?>
				<form action="<?php print $PHP_SELF?>" method="post">
                		<h2 style="color:#ff0000">There was an error creating your certificate.</h2><br>
	                	<blockquote>
	                	<h3>Debug Info:</h3>
				<pre><?php print $errtxt ?></pre>
				</blockquote>
				<p>
				<?php print $hidden_fields?>
				<input type="submit" name="submit" value="Back">
				<p>
				</form>
				<?php

				printFooter();
				break;
        		}
        		else {
				$serial = $errtxt;
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
                        upload($config['pfx_dir']."/$serial.pfx", $common_name." ($email).p12", 'application/x-pkcs12');
                        break;
                }

		break;
	}	
	
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
	
	<form action="<?php print $PHP_SELF?>" method="post" name="request">
	<div>
	<table style="width:99%">
	<tr><th colspan="2"><h2>Certificate Request Form</h2></th></tr>

	<tr>
	<td width=30%>Common Name<br>(i.e. User real name or computer hostname) </td>
	<td><input type="text" name="common_name" value="<?php print  htvar($common_name)?>" size="50" maxlength="60"></td>
	</tr>

	<tr>
	<td>E-mail Address </td>
	<td><input type="text" name="email" value="<?php print htvar($email)?>" size="50" maxlength="60"></td>
	</tr>

	<tr>
	<td>Organization (Company/Agency)</td>
	<td><input type="text" name="organization" value="<?php print htvar($organization)?>" size="60" maxlength="60"></td>
	</tr>

	<tr>
	<td>Department/Unit </td><td><input type="text" name="unit" value="<?php print  htvar($unit) ?>" size="40" maxlength="60"></td>
	</tr>

	<tr>
	<td>Locality (City/County)</td><td><input type="text" name="locality" value="<?php print  htvar($locality) ?>" size=30 maxlength=30></td>
	</tr>

	<tr>
	<td>State/Province</td><td><input type="text" name="province" value="<?php print  htvar($province) ?>" size=30 maxlength=30></td>
	</tr>

	<tr>
	<td>Country</td>
	<td><input type="text" name="country" value="<?php print  htvar($country) ?>" size=2 maxlength=2></td>
	</tr>

	<tr>
	<td>Certificate Password </td>
	<td><input type="password" name="passwd" value="<?php print  htvar($passwd) ?>" size="30">&nbsp;&nbsp; Again <input type=password name=passwdv  value="<?php print  htvar($passwdv) ?>" size=30></td>
	</tr>

	<tr>
	<td>Certificate Life </td>
	<td><select name="expiry">
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
	<td><select name="keysize">
	<?php
	for ( $i = 512 ; $i < 4096 ; $i+= 512 ) {
		print "<option value=$i " . ($keysize == $i ? "selected='selected'" : "") . " >$i bits</option>\n" ;
	}

	?>
	</select></td>
	</tr>

	<tr>
	<td>Certificate Use: </td>
	<td><select name="cert_type">
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
	<td>
	<div style="text-align:center">
	<input type="submit" style="display: inline;" name="submit" value="Submit Request">
	<input type="hidden" name="form_stage" value="validate">
	
	
	</form>
	
	<form action="<?php print $PHP_SELF?>" method="post" name="request2" style="display: inline-block" >
	<div>
	<input type="submit" name="submit2" value="Clear All">
	<input type="hidden" name="form_stage" value="clear">
	</div>
	</form>
	<form action="<?php print $PHP_SELF?>" method="post" name="request3" style="display: inline-block" >
	<div>
	<input type="submit" name="submit3" value="Go Back">
	<input type="hidden" name="form_stage" value="back_to_menu">
	</div>
	</form>
	</div>
	</div>
	</td>
	<td style="color: red">* All fields are required</td>
	</tr>
	</table>
	<?php

	printFooter();
endswitch;

?>
