<script type="text/javascript">
function loadPage()
{
    if (document.login)//if the form login exists, focus:
    {
        document.login.name.focus();//the username input
        document.login.pass.focus();//the password input
        document.login.login.focus();//the login button (submitbutton)
    }
}
</script>

<?php
include('../config.php');
include(STORE_DIR.'/config/config.php');
include('../include/my_functions.php');
include('../include/common.php');
include('../include/openssl_functions.php');

$stage     = gpvar('stage');
$serial    = gpvar('serial');
$sortfield = gpvar('sortfield');
$ascdec    = gpvar('ascdec');
$passwd    = gpvar('passwd');
$expiry    = gpvar('expiry');
$submit    = gpvar('submit');
$dl_type   = gpvar('dl_type');

$common_name  = gpvar('common_name');
$email		  = gpvar('email');
$passwd		  = gpvar('passwd');

$search       = gpvar('search');
$show_valid   = gpvar('show_valid');
$show_revoked = gpvar('show_revoked');
$show_expired = gpvar('show_expired');

# Email variables
$sender_addr   = gpvar('sender_addr');
$sender_alias  = gpvar('sender_alias');
$email_subject = gpvar('email_subject');
$email_text    = gpvar('email_text');
$to_addr       = gpvar('to_addr'); 

$email_attachment = gpvar('email_attachment');

# Prevent handling certs that don't belong to user
#if ($serial && CAdb_issuer($serial) != $PHPki_user && ! in_array($PHPki_user, $PHPki_admins)) { 
#	$stage = 'goaway';
#}

if ( !($show_valid.$show_revoked.$show_expired) ) {
	$show_valid   = 'V';
	$show_revoked = 'R';
	$show_expired = 'E';
}

$qstr_filter = 'search='.htvar($search).'&'.
		"show_valid=$show_valid&".
		"show_revoked=$show_revoked&".
		"show_expired=$show_expired&";

$qstr_sort   = "sortfield=$sortfield&ascdec=$ascdec";

switch ($stage) {
case 'goaway':
	printHeader(false);
	?> <p><div style="text-align:center"><h2 style="color:red">YOU ARE A VERY BAD BOY!</h2></div> 
	<?php
	break;

case 'display':
	printHeader('ca');
	print "<div style='float:left; position: absolute'><a href='manage_certs.php'><button class='btn'>Go Back</button></a></div>";
	
	?>
    <div style="text-align:center"><h2>Certificate Details</h2></div>
	<div style="text-align:center"><h3 style="color:#0000AA">(#<?php echo $serial ?>)<br><?php echo htvar(CA_cert_cname($serial).' <'.CA_cert_email($serial).'>') ?> </h3></div>
	<?php

	if ($revoke_date = CAdb_is_revoked($serial))
		print '<div style="text-align:center"><h2 style="color:red">REVOKED '.$revoke_date.'</h2></div>';
			
    print '<pre>'.CA_cert_text($serial).'</pre>';
    print "<br><div style='float:left; position: absolute'><a href='manage_certs.php'><button class='btn'>Go Back</button></a></div>";
	break;

case 'dl-confirm':
	printHeader('ca');

	$rec = CAdb_get_entry($serial);
	?>
	<h3>You are about to download the <font color="red">PRIVATE</font> certificate key for <?php echo htvar($rec['common_name']).' &lt;'.htvar($rec['email']).'&gt; '?></h3>
	<h3 style="color:red">DO NOT DISTRIBUTE THIS FILE TO THE PUBLIC!</h3>
	<form action="<?php echo htvar($PHP_SELF).'?stage=download&serial='.$serial.'&'.$qstr_sort.'&'.$qstr_filter?>" method="post">
	<strong>File type: </strong>
	<select class="inputbox" name="dl_type">
	<option value="PKCS#12">PKCS#12 Bundle</option>
	<option value="PKCS#12-OPENVPN-ZIP">Zipped PKCS#12 and OpenVPN config</option>
	<option value="PKCS#12-OPENVPN-TBLK">Zipped Tunnelblick folder (PKCS#12 and OpenVPN config)</option>	
	<option value="PEMCERT">PEM Certificate</option>
	<option value="PEMKEY">PEM Key</option>
	<option value="PEMBUNDLE">PEM Bundle</option>
	<option value="PEMCABUNDLE">PEM Bundle w/Root</option>
	</select>
	<input class="btn" type="submit" name="submit" value="Download">
	&nbsp; or &nbsp;
	<input class="btn" type="submit" name="submit" value="Go Back">
	</form>
	<?php
	break;

case 'download':
	if (strstr($submit, "Back"))  $dl_type = '';

	$rec = CAdb_get_entry($serial);

	switch ($dl_type) {
	case 'PKCS#12':
		upload("$config[pfx_dir]/$serial.pfx", "$rec[common_name]_($rec[email]).p12", 'application/x-pkcs12');
		break;
	case 'PKCS#12-OPENVPN-ZIP':
		CA_create_openvpn_archive($serial, $rec['common_name'], $rec['email']);
		upload("$config[openvpn_archives_dir]/$rec[common_name]_($rec[email]).zip", "$rec[common_name]_($rec[email]).zip", 'application/zip');
		break;
	case 'PKCS#12-OPENVPN-TBLK':		
		$attachment = $config['openvpn_archives_dir']."/".$rec["common_name"] . "_(" . $rec["email"] . ").tblk.zip";
		if (! file_exists($attachment)) {
			CA_create_Tunnelblick_zip($serial, $rec['common_name'], $rec['email']);
		}		
		/* I was using this before but I noticed it was messing up the archive
		if (headers_sent()) {
	    	echo 'HTTP header already sent';
		} else {
		    if (!is_file($attachment)) {
		        header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
		        echo 'File not found';
		    } else if (!is_readable($attachment)) {
		        header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
		        echo 'File not readable';
		    } else {
		        header($_SERVER['SERVER_PROTOCOL'].' 200 OK');
		        header("Content-Type: application/zip");
		        header("Content-Transfer-Encoding: Binary");
		        header("Content-Length: ".filesize($attachment));
		        header("Content-Disposition: attachment; filename=\"".basename($attachment)."\"");
		        readfile($attachment);
		        exit;
		    }
		} */
		upload("$config[openvpn_archives_dir]/$rec[common_name]_($rec[email]).tblk.zip", "$rec[common_name]_($rec[email]).tblk.zip", 'application/zip');
		break;
	case 'PEMCERT':
		upload("$config[new_certs_dir]/$serial.pem", "$rec[common_name]_($rec[email]).pem",'application/pkix-cert');
		break;
	case 'PEMKEY':
		upload("$config[private_dir]/$serial-key.pem", "$rec[common_name]_($rec[email])-key.pem",'application/octet-stream');
		break;
	case 'PEMBUNDLE':
		upload(array("$config[private_dir]/$serial-key.pem","$config[new_certs_dir]/$serial.pem"), "$rec[common_name]_($rec[email]).pem",'application/octet-stream');
		break;
	case 'PEMCABUNDLE':
		upload(array("$config[private_dir]/$serial-key.pem","$config[new_certs_dir]/$serial.pem",$config['cacert_pem']), "$rec[common_name]_($rec[email]).pem",'application/octet-stream');
		break;
	default:
		header("Location: ${PHP_SELF}?$qstr_sort&$qstr_filter");
	}
	break;

case 'revoke-form':
	$rec = CAdb_get_entry($serial);

	printHeader('ca');

	?>
	<h4>You are about to <font color="red">REVOKE</font> the following certificate:</h4>
       	<table style="width:500px"><tr>
       	<td style='white-space: nowrap; width:25%'>
       	<p align="right">
		Serial Number<br>
       	User's Name<br>
       	Email Address<br>
       	Organization<br>
       	Department/Unit<br>
       	Locality<br>
       	State/Province<br>
       	Country<br>
       	</td>
	<?php

	print '
       	<td>
	'.htvar($rec["serial"]).'<br>
       	'.htvar($rec["common_name"]).'<br>
       	'.htvar($rec["email"]).'<br>
       	'.htvar($rec["organization"]).'<br>
       	'.htvar($rec["unit"]).'<br>
       	'.htvar($rec["locality"]).'<br>
       	'.htvar($rec["province"]).'<br>
       	'.htvar($rec["country"]).'<br>
       	</td>
       	</tr></table>
	<h4>Are you sure?</h4>
       	<p><form action="'.htvar($PHP_SELF).'?'.$qstr_sort.'&'.$qstr_filter.' method="post">
	<input type="hidden" name="stage" value="revoke" >
	<input type="hidden" name="serial" value='.$serial.' >
       	<input class="btn" type="submit" name="submit" value="Yes" >&nbsp
       	<input class="btn" type="submit" name="submit" value="Cancel">
       	</form>';
	
	break;

case 'revoke':
	$ret = true;
	if ($submit == 'Yes') 
		list($ret, $errtxt) = CA_revoke_cert($serial);

	if (! $ret) {
		printHeader('ca');

		print "<form action=".htvar($PHP_SELF)."?stage=revoke-form&serial=$serial&$qstr_sort&$qstr_filter method=post>";
		?>
		<h2 style="color:#ff0000">There was an error revoking your certificate.</h2>
		<blockquote>
		<h3>Debug Info:</h3>
		<pre><?php echo $errtxt ?></pre>
		</blockquote>
		<p>
		<input class="btn" type="submit" name="submit" value="Go Back">
		<p>
		</form>
		<?php
	}
	else
		header("Location: ${PHP_SELF}?$qstr_sort&$qstr_filter");
	break;

case 'renew-form':
	# 
	# Get last known values submitted by this user.  We only really
	# need the expiry value, but the old cert values will override
	# the rest.
	#
	if (! $submit and file_exists("config/user-${PHPki_user}.php"))
		include("config/user-${PHPki_user}.php");

	# 
	# Get values from the old certificate.
	#
	$rec = CAdb_get_entry($serial);
	$country      = $rec['country'];
	$province     = $rec['province'];
	$locality     = $rec['locality'];
	$organization = $rec['organization'];
	$unit         = $rec['unit'];
	$common_name  = $rec['common_name'];
	$email        = $rec['email'];

	printHeader('ca');
	?>
	
	<body onLoad="self.focus();document.form.passwd.focus()">

	<form action="<?php echo htvar($PHP_SELF).'?'.$qstr_sort.'&'.$qstr_filter ?>" method="post" name="form">
	<table style="width:99%">	
	<tr>
	<th colspan="2">Certificate Renewal Form</th>
	</tr>
	<tr>
	<td width="25%">Common Name </td>
	<td><input class="inputbox" type="text" name="common_name" value="<?php echo htvar($common_name)?>" size=50 maxlength=60 disabled></td>
	</tr>

	<tr>
	<td>E-mail Address </td>
	<td><input class="inputbox" type="text" name="email" value="<?php echo htvar($email)?>" size=50 maxlength=60 disabled></td>
	</tr>

	<tr>
	<td>Organization </td>
	<td><input class="inputbox" type="text" name="organization" value="<?php echo htvar($organization)?>" size=60 maxlength=60 disabled></td>
	</tr>

	<tr>
	<td>Department/Unit </td><td><input class="inputbox" type="text" name="unit" value="<?php echo htvar($unit) ?>" size=40 maxlength=60 disabled></td>
	</tr>

	<tr>
	<td>Locality</td><td><input class="inputbox" type="text" name="locality" value="<?php echo htvar($locality) ?>" size=30 maxlength=30 disabled></td>
	</tr>

	<tr>
	<td>State/Province</td><td><input class="inputbox" type="text" name="province" value="<?php echo htvar($province) ?>" size=30 maxlength=30 disabled></td>
	</tr>

	<tr>
	<td>Country</td>
	<td><input class="inputbox" type="text" name="country" value="<?php echo htvar($country) ?>" size=2 maxlength=2 disabled></td>
	</tr>

	<tr>
	<td>Password <br>(for encrypting the PKCS#12 file and decrypting the private key if it is encrypted)</td>
	<td><input class="inputbox" type="password" name="passwd" value="<?php echo htvar($passwd) ?>" size=30></td>
	</tr>

	<tr>
	<td>Certificate Life </td>
	<td><select class="inputbox" name="expiry">
	<?php

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
	<td>
	<div style="text-align:center">
	<input class="btn" type="submit" name="submit" value="Submit Request">
	<input class="btn" type="submit" name="submit" value="Go Back"></div>
	</td>
	<td>
	<input type="hidden" name="stage" value="renew">
	<input type="hidden" name="serial" value="<?php echo $serial?>">
	</td>
	</tr>
	</table>
	</form>
	<?php

	printFooter();
	break;

case 'renew':
	$ret = true;
	if ($submit == "Submit Request") {
		$rec = CAdb_get_entry($serial);
		list($ret, $errtxt, $pwd_use) = CA_renew_cert($serial, $expiry, $passwd);

		if (! $ret) {
			printHeader('ca');
	
			print "<form action=".htvar($PHP_SELF)."?stage=renew-form&serial=$serial&$qstr_sort&$qstr_filter method=post>";
			?>
			<h2 style="color:#ff0000">There was an error creating your certificate.</h2><br>
			<blockquote>
			<h3>Debug Info:</h3>
			<pre><?php echo $errtxt?></pre>
			</blockquote>
			<p>
			<input class="btn" type="submit" name="submit" value="Back">
			<p>
			</form>
			<?php
		}
		else {
			log_password_entry($config['passwd_log'], $rec['common_name'], $rec['email'], $passwd, $pwd_use);
			header("Location: ".htvar($PHP_SELF)."?$qstr_sort&$qstr_filter");
		}
	}
	else 
		header("Location: ".htvar($PHP_SELF)."?$qstr_sort&$qstr_filter");
	break;
	
case 'request-send-email':
	
	printHeader('ca');

	# 
	# Get values from the certificate.
	#
	$rec = CAdb_get_entry($serial);
	$country      = $rec['country'];
	$province     = $rec['province'];
	$locality     = $rec['locality'];
	$organization = $rec['organization'];
	$unit         = $rec['unit'];
	$common_name  = $rec['common_name'];
	$email        = $rec['email'];
	$status 	  = $rec['status'];

	# Set some default values for sender
	if (!isset($sender_addr) || $sender_addr == '') $sender_addr = $config['contact']; # or eg. gethostname();
	if (!isset($sender_alias) || $sender_alias == '') $sender_alias = $config['contact']; # or eg. gethostname();
	if (!isset($email_text) || $email_text == '') $email_text = " ";
	if (!isset($to_addr) || $to_addr == "") $to_addr = $email;
	?>
	<div style="align:center">
	<h4>You are about to email to the user below with a zip archive containing <span style="color:red">PKCS#12 and OpenVPN config</span>.</h4>
	 	User certificate details:
	 	<table style="width:400px">
       	<tr>
       	<td style='white-space: nowrap; width:30%'>
       	<p align="right">
		Serial Number<br>
       	User's Name<br>
       	Email Address<br>
       	Organization<br>
       	Department/Unit<br>
       	Locality<br>
       	State/Province<br>
       	Country<br>
       	Status<br>
       	</td>

	<?php 
	print '
       	<td>
	'.htvar($rec["serial"]).'<br>
       	'.htvar($rec["common_name"]).'<br>
       	'.htvar($rec["email"]).'<br>
       	'.htvar($rec["organization"]).'<br>
       	'.htvar($rec["unit"]).'<br>
       	'.htvar($rec["locality"]).'<br>
       	'.htvar($rec["province"]).'<br>
       	'.htvar($rec["country"]).'<br>
       	'.htvar($rec["status"]).'<br>
       	</td>
       	</tr></table>'
       	?>
    <br>
    <?php  
	print 		
		'<form action="'.htvar($PHP_SELF).'?'.$qstr_sort.'&'.$qstr_filter.' method="post">'.
		'From:<br>
		<input class="inputbox" type="text" name="sender_addr" value="'. $sender_addr .'" style="width:400px"><br>'.
		'Sender name:<br>
		<input class="inputbox" type="text" name="sender_alias" value="'. $sender_alias .'" style="width:400px"><br>'.
		'To:<br>
		<input class="inputbox" type="text" name="to_addr" value="'. $to_addr .'" style="width:400px"><br>'.
		'Subject:<br>
		<input class="inputbox" type="text" name="email_subject" value="'. $email_subject .'" style="width:400px"><br>'.
		'Email Message:<br>
		<textarea class="inputbox" name="email_text" rows="7" style="width:400px">'. $email_text .'</textarea><br>'.
		'Attachment:<br> 
		<select class="inputbox" name="email_attachment">
			<option value="ovpn_zip">OpenVPN Zip Bundle</option>
			<option value="ovpn_tblk">OpenVPN Tunnelblick Bundle</option>
		</select>';
	?>
    
	<h4>Are you sure?</h4>
       	<p>
		<input type="hidden" name="stage" value="send-email" >
		<input type="hidden" name="serial" value="<?php print $serial ?>" >
	    <input class="btn" type="submit" name="submit" value="Send email" >
       	<input class="btn" type="submit" name="submit" value="Cancel">
       	</form>
<?php

	break;

case 'send-email':

	if ($submit == 'Send email') {		
		#print $sender_alias;
		$rec = CAdb_get_entry($serial);
		
		if ($email_attachment == "ovpn_zip") {
			$attachment = $config['openvpn_archives_dir']."/".$rec["common_name"] . " (" . $rec["email"] . ").zip";
			if (! file_exists($attachment)) {
				CA_create_openvpn_archive($serial, $rec['common_name'], $rec['email']);
			}
		}
		else if ($email_attachment == "ovpn_tblk") {
			$attachment = $config['openvpn_archives_dir']."/".$rec["common_name"] . " (" . $rec["email"] . ").tblk.zip";
			if (! file_exists($attachment)) {
				CA_create_Tunnelblick_zip($serial, $rec['common_name'], $rec['email']);
			}
		}
		
		if(is_email($to_addr) && send_email($sender_addr, $sender_alias, $to_addr, $email_subject, $email_text, $attachment)) {
			echo "<script type='text/javascript'>alert('Email sent.');
			window.location.href = '${PHP_SELF}?$qstr_sort&$qstr_filter';
			</script>";
		}
		else 
			# This method may make the page flicker because it refreshes it, might want to implement an ajax call instead
			echo "<script type='text/javascript'>
			alert('Error: Email was not sent, check for missing or incorrect fields.');
			window.location.href = '${PHP_SELF}?stage=request-send-email&serial=$serial';
			</script>";
		
	}

default:

	printHeader('ca');	
	?>
	<body onLoad="self.focus();document.filter.search.focus()">
	<div style="text-align: center">
	<table style="margin: 0 auto">
	<tr><th colspan="10"><h2>Certificate Management Control Panel</h2></th></tr>
	<tr><td colspan="10"><div style="text-align:center">
	<form action="<?php echo htvar($PHP_SELF)."?$qstr_sort"?>" method="get" name="filter"> 
	Search: 
		<input class="inputbox" type="text" name="search" value="<?php echo htvar($search)?>" style="font-size: 11px;" maxlength="60" size="35">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <input type="checkbox" name="show_valid" value="V" <?php echo ($show_valid?'checked':'')?>>Valid &nbsp;&nbsp;
        <input type="checkbox" name="show_revoked" value="R" <?php echo ($show_revoked?'checked':'')?>>Revoked &nbsp;&nbsp;
        <input type="checkbox" name="show_expired" value="E" <?php echo ($show_expired?'checked':'')?>>Expired &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <input class="btn" type="submit" name="submit" value="Apply Filter" style="font-size: 11px;">
        </form>
	</div></td>
	</tr>
	<?php

	if (! $sortfield) {
		$sortfield = 'email' ;
		$ascdec = 'A';
	}

	if ($ascdec == 'A') {
		$arrow_gif = '../images/blue-go-up-th.png';
		$ht_ascdec = 'D';
	}
	else {
		$arrow_gif = '../images/blue-go-down-th.png';
		$ht_ascdec = 'A';
	}

	print '<tr>';
	$headings = array(
		'status'=>"Status", 'issued'=>"Issued", 'expires'=>"Expires",
		'common_name'=>"User's Name", 'email'=>"E-mail", 
		'organization'=>"Organization", 'unit'=>"Department", 
		'locality'=>"Locality", 'matches_ca'=>"Matches CA",
	);

	foreach($headings as $field=>$head) {
		print '<th><a href="'.htvar($PHP_SELF).'?sortfield='.$field.'&ascdec=A&'.$qstr_filter.'" title="Click to sort by this column."><u>'.$head.'</u></a>';

		if ($sortfield == $field) {
			print	'&nbsp<a href="'.htvar($PHP_SELF).'?sortfield='.$field.'&ascdec='.$ht_ascdec.'&'.$qstr_filter.'" >'.
				'<img src='.$arrow_gif.' height=12 alt=\'Change sort order.\' title=\'Click to reverse sort order.\'></a>';
		}

		print '</th>';
	}
	print '<th><a href="" title="Pick an action to perform to certificate.">Action</a></th>';
	print '</tr>';
	
	$x = "^[${show_valid}${show_revoked}${show_expired}]";

	#if (in_array($PHPki_user, $PHPki_admins)) {
		$x = "$x.*$search";
	#}
	#else {
	#	$x = "$x.*$search.*$PHPki_user|$x.*$PHPki_user.*$search";
	#}
	
    $db = csort(CAdb_to_array($x), $sortfield, ($ascdec=='A'?SORT_ASC:SORT_DESC));
 
	$stcolor = array('Valid'=>'green', 'Revoked'=>'red', 'Expired'=>'orange');
	
    foreach($db as $rec) {
    	 
		print '<tr style="font-size: 11px;">
			 <td><font color='.$stcolor[$rec['status']].'><b>' .$rec['status'].'</b></font></td>
			 <td style="white-space: nowrap">'.$rec['issued'].'</td>
			 <td style="white-space: nowrap">'.$rec['expires'].'</td>
			 <td>'.$rec['common_name'].'</td>
			 <td style="white-space: nowrap">
				<a href="mailto:' . htvar($rec['common_name']) . ' <' . htvar($rec['email']) . '>" >' . htvar($rec['email']) . '</a>
			 </td>
			 <td>'.htvar($rec['organization']).'</td>
			 <td>'.htvar($rec['unit']).'</td>
			 <td>'.htvar($rec['locality']).'</td>
    		 <td>'.CA_verify_match_cert($rec['serial']).'</td>
			 <td><a href="'.htvar($PHP_SELF).'?stage=display&serial='.$rec['serial'].'">'.
			 '<img src="../images/display.png" alt="Display" title="Display complete certificate details."></a>';

		if ($rec['status'] == 'Valid') {
			print '
			<a href="'.htvar($PHP_SELF).'?stage=dl-confirm&serial='.$rec['serial'].'&'.$qstr_sort.'&'.$qstr_filter.'">'.
			'<img src="../images/download.png" alt="Download" title="Download the PRIVATE certificate. DO NOT DISTRIBUTE THIS TO THE PUBLIC!"></a>'.
			'<a href="'.htvar($PHP_SELF).'?stage=request-send-email&serial='.$rec['serial'].'&'.$qstr_sort.'&'.$qstr_filter.'">'.
			'<img src="../images/email.png" alt="Email" title="Email the archive to the user."></a>'.
			'<a href="'.htvar($PHP_SELF).'?stage=revoke-form&serial='.$rec['serial'].'&'.$qstr_sort.'&'.$qstr_filter.'">'.
			'<img src="../images/revoke.png" alt="Revoke" title="Revoke the certificate when the e-mail address is no longer valid or the certificate password or private key has been compromised."></a>';
		} 
		
		print '
		<a href="'.htvar($PHP_SELF).'?stage=renew-form&serial='.$rec['serial'].'&'.$qstr_sort.'&'.$qstr_filter.'">'.
		'<img src="../images/view-refresh-th.png" alt="Renew" title="Renew the certificate by revoking it, if necessary, and creating a replacement with a new expiration date."></a></td></tr>';
		
		
    }
?>
	</table>
	</div>
<?php
	printFooter();
}
?>
