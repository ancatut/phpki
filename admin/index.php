<?php

include('../config.php');
include(STORE_DIR.'/config/config.php');
include('../include/my_functions.php');
include('../include/common.php');
include('../include/openssl_functions.php');

function md5_for_config($val) { return "md5('".$val."')"; }
$stage   = gpvar('stage');
$user_id = gpvar('user_id');
$passwd  = gpvar('passwd');
$passwdv = gpvar('passwdv');
$expiry  = gpvar('expiry');
$submit	 = gpvar('submit');
$user_group = gpvar('user_group');

switch($stage) {
case 'list_users':
	printHeader('admin');
        print '<p><h3>Contents of htpasswd file:</h3><pre>';
        readfile($config['passwd_file']);
        print '</pre>';
        print '<p><h3>Contents of htgroups file:</h3><pre>';
        readfile($config['groups_file']);
        print '</pre>';

	?>
	<form action="<?php echo htvar($PHP_SELF)?>" method="post">
	<input class="btn" type="submit" name="submit" value="Back to Menu">
	</form>
	<?php
	printFooter(false);
	break;

case 'add_user_form':
	printHeader('admin');
	?>
	<body onLoad="self.focus();document.form.user_id.focus()">
	<form action="<?php echo htvar($PHP_SELF)?>" method="post" name="form">
	<table>
	<tr><th colspan="2"><h3>Add User or Update Existing User's Password or User Group</h3></th></tr>
	<tr><td>User ID</td><td><input class="inputbox" type="text" name="user_id" value="<?php echo htvar($user_id)?>" maxlength=15 size=15></td></tr>
	<tr>
		<td>User Group (if the user already exists, it will update to this value)</td>
		<td>
		<select class="inputbox" name="user_group">
			<option value="admin">admin</option>
			<option value="cert-manager">cert-manager</option>
			<!--<option value="regular-user">regular-user</option>-->
			</select>
		</td>
	</tr>
	<tr><td>Password (min. 8 characters long) (updates to the new value if user already exists)</td><td><input class="inputbox" type="password" name="passwd" value='' size="25"></td></tr>
	<tr><td>Verify Password </td><td><input class="inputbox" type="password" name="passwdv" value='' size="25"></td></tr>
	</table>
	<input class="inputbox" type="hidden" name="stage" value="add_user">
	<input class="btn" type="submit" name="submit" value='Submit'>
	<input class="btn" type="submit" name="submit" value='Back to Menu'>
	</form>
	<?php
	printFooter();
	break;

case 'add_user':
	printHeader('admin');
	if ($submit == "Back to Menu")
		header("Location: index.php");
	else {
	if (! $passwd || ! $passwdv || $passwd != $passwdv || strlen($passwd) < 8 || $user_id == "") {
		print '<div style="text-align:center"><h2 style="font-color:red">Error: You have supplied incomplete or invalid information.</h2></div>';

		?>
		<p><div style="text-align: center">
		<form action="<?php echo htvar($PHP_SELF)?>" method="post">
		<input type="hidden" name="stage" value="add_user_form">
		<input type="hidden" name="user_id" value="<?php echo htvar($user_id)?>">
		<input class="btn" type="submit" name="submit" value="Back">
		</form></div>
		<?php
	}
	else if (! username_validchars($user_id)) 
	{
		print '<div style="text-align:center"><h2 style="font-color:red">Invalid characters in username.</h2></div>';
	}
	else 
	{	
		$groups_file = $config['groups_file'];
		echo "Checking if user is in $groups_file under $user_group, otherwise adding them...<br><br>";
			
		update_groupfile($user_id, $user_group, "add_user");
		
		$pwdfile = escapeshellarg($config['passwd_file']);
		$user_id = escapeshellarg($user_id);
		$passwd  = escapeshellarg($passwd);
		
		print 'Writing user password. Results of htpasswd command:<br>';
		system("htpasswd -b $pwdfile $user_id $passwd 2>&1");
		print "<br><br>";
		
		print "Contents of groups file:<pre>";
		print file_get_contents($groups_file);
		print "</pre>";
		?>
		<p>
		<form action="<?php echo htvar($PHP_SELF)?>" method="post">
		<input class="btn" type="submit" name="submit" value="Back to Menu">
		</form>
		<?php
	}
	}
	printFooter();
	break;

case 'del_user_form':
	printHeader('admin');
	?>
	<br><br>
	<body onLoad="self.focus();document.form.login.focus()">
	<form action="<?php echo htvar($PHP_SELF)?>" method="post" name="form">
	<table class="menu" style="width:40%">	
	<tr><th colspan="2"><h3>Remove User</h3></th></tr>
	<tr><td colspan="2">
	<?php
	print '<b>Contents of htgroups file:</b><pre>';
        readfile($config['groups_file']);
        print '</pre>';
        ?>
    </td></tr>
	<tr><td><b>User to Remove:</b></td><td><input class="inputbox" type="text" name="user_id" value="<?php echo htvar($user_id)?>" maxlength="15" size="15"></td></tr>
	</table>
	<br>
	<div style="text-align:center">
	<input type="hidden" name="stage" value="del_user">
	<input class="btn" type="submit" name="submit" value='Submit'>
	<input class="btn" type="submit" name="submit" value="Back to Menu">
	</div>
	</form>
	<?php
	printFooter();
	break;
case 'del_user':
	printHeader('admin');
	 
	if ($user_id != "" && username_validchars($user_id)) {
		update_groupfile($user_id, $user_group, "del_user");
				
		print "Removing user from groups file.<br><br>";
		$pwdfile = escapeshellarg($config['passwd_file']);
		$user_id = escapeshellarg($user_id);
	
		print 'Results of htpasswd command:<br>';
		system("htpasswd -D $pwdfile $user_id 2>&1");
		?>
			<p>
			<form action="<?php echo htvar($PHP_SELF)?>" method="post">
			<input class="btn" type="submit" name="submit" value="Back to Menu">
			</form>
		<?php 
	}
	else if ($submit == "Submit") { 
		print "Error: Please enter a valid username.";
	?>
		<p>
		<form action="<?php echo htvar($PHP_SELF)?>?stage=del_user_form" method="post">
		<input class="btn" type="submit" name="submit" value="Back">
		</form>			 
	<?php
	}
	else header("Location: index.php");
	
	printFooter();
	break;

/* Work in progress:
case 'import_CA_confirm':
	break;
case 'import_CA':
		printHeader('ca');
		# Save the names of the uploaded files
		$CA_pkcs12_tmp_name = $_FILES['CA_pkcs12']["tmp_name"];
		$CA_pkcs12_name = $_FILES['CA_pkcs12']["name"];
		$CA_privkey_tmp_name = $_FILES['CA_privkey']["tmp_name"];
		$CA_privkey_name = $_FILES['CA_privkey']["name"];
		$CA_cert_tmp_name = $_FILES['CA_cert']["tmp_name"];
		$CA_cert_name = $_FILES['CA_cert']["name"];
	
		# Check which upload fields have been completed
	
		# Uploaded PKCS#12 file
		if (isset($_FILES['CA_pkcs12']) && $CA_pkcs12_tmp_name != "") {
			if (check_uploaded_filename($CA_pkcs12_name)) {
					
				$name = "ca.pfx";
	
				# Check filename extension
				$fileinfo = finfo_open( FILEINFO_MIME_TYPE );
				$mime_type = finfo_file( $fileinfo, $CA_pkcs12_tmp_name );
				echo $mime_type;
				finfo_close( $fileinfo );
					
				### Object-oriented approach. See top comment at http://php.net/manual/en/features.file-upload.php
				#$finfo = finfo_open(FILEINFO_MIME);
				#echo finfo_file($finfo, sys_get_temp_dir()."/".$CA_pkcs12_tmp_name);
				#if (finfo_file($finfo, sys_get_temp_dir()."/".$CA_pkcs12_tmp_name) == "application/x-pkcs12")
				#	echo "Correct.";
					#else echo "Wrong format.";
					#if (false === $ext = array_search(
					#		finfo_file($finfo, sys_get_temp_dir()."/".$CA_pkcs12_tmp_name),
					#		array(
					#				'pfx' => 'application/x-pkcs12',
					#				'p12' => 'application/pkcs12',
					#		),
					#		true
					#		)) {
					#			echo "Wrong format.";
						#			throw new RuntimeException('Invalid file format.');
						#		}
						finfo_close($finfo);
						### Functional approach. This seems to be the same thing?
						if (isset(pathinfo($CA_pkcs12_name)['extension'])) {
	
							echo $CA_pkcs12_tmp_name."\n";
							$CA_pkcs12_ext = pathinfo($CA_pkcs12_name)['extension'];
							if ($CA_pkcs12_ext == 'p12' || $CA_pkcs12_ext == 'pfx') {
	
								?>
			<form action="<?php echo htvar($PHP_SELF)?>?stage=import_CA_confirm" method="POST">
			Please enter PKCS#12 password: <input type='password'>
			<input type='submit' name='submit' value='Confirm'>
			</form>
	<?php
			//check_pkcs12($tmp_name);
			try {
				//echo sys_get_temp_dir();
				
				$output = shell_exec(OPENSSL." pkcs12 -in ". escshellarg($CA_pkcs12_tmp_name) ." -info -noout -passin pass:12345678 2>&1");
				echo "<pre>$output</pre>";
				
				//flush_exec(PKCS12." -in ". escshellarg(sys_get_temp_dir()."/".$tmp_name)." -info -noout");
				move_uploaded_file($CA_pkcs12_tmp_name, "/var/www/uploads/$name");
				echo "File ".$name." has been uploaded correctly.\n";
				} catch(Exception $e) {
					echo "File ".$name." has not been uploaded correctly.\n Caught exception: ".$e->getMessage();
				}
			}
			else {
				echo "Wrong file format. Please try again.\n";
			}
		}
		else {
			echo "Wrong file format. Please try again.\n";
		}
	}
	else echo "File name contains illegal characters, please try again.";
	}
	elseif (isset($_FILES['CA_privkey']) && $CA_privkey_tmp_name != "") {
		# Uploaded private key file and CA certificate
		if (isset($_FILES['CA_cert']) && $CA_cert_tmp_name != "") {
			echo "Attached CA_privkey and CA_cert.";
		}
		# Uploaded only private key file
		else {
			echo "Attached CA_privkey.";
		}
	}
	# The necessary files are missing
	else {
		echo "Please attach the missing files.";
	}
	break;
*/
	
case 'renew_CA_confirm':
	printHeader('admin');
	?>
	
	<h3>Renewing the CA Certificate</h3>
	This process will backup the old CA certificate that is about to expire and create a new certificate with the same serial number and a new validity duration.<br><br>
	Please select the lifetime of the renewed CA certificate:<br><br>
	<form action="<?php print htvar($PHP_SELF)?>?stage=renew_CA" method="POST">
	<input type="hidden" name="stage" value="renew_CA">
		<select class="inputbox" name="expiry">
		<?php
		for ( $i = 5 ; $i < 20 ; $i+=5 ) {
			print "<option value=$i " . ($expiry == $i ? "selected='selected'" : "") . " >$i Years</option>\n" ;
		}
		?>
		</select>		
		<input class="btn" type="submit" name="submit" value="Confirm & Renew CA Cert">
		<input class="btn" type="submit" name="submit" value="Back to Menu">	
	</form>
	<?php
	break;
	
case 'renew_CA':
	printHeader('admin');
	if ($submit == "Confirm & Renew CA Cert") {

		echo "<h3>Renewing the CA Certificate</h3>";
		CA_renew_CAcert($expiry);
		?>
		<br><br>
		<form action="" method="POST">
		<input class="btn" type="submit" name="stage" value="Back to Menu">
		<input type="hidden" name="stage" value="back_to_menu">
		</form>
		<?php 
	}
	else 
		header("Location: index.php");
	break;

case "back_to_menu":
	header("Location: index.php");
	break;

default:
	printHeader('admin');
	?>
	<br>
	<br>
	<div style="text-align:center">
	<table class="menu" style="width:50%"><tr><th style="font-size: 24px"><h2>SYSADMIN MENU</h2></th></tr>
	<tr><td><a href="<?php echo htvar($PHP_SELF)?>?stage=add_user_form"><strong>Add User or Update Existing User's Password or Group</strong></a></td></tr>
	<tr><td><a href="<?php echo htvar($PHP_SELF)?>?stage=del_user_form"><strong>Remove User</strong></a></td></tr>
	<tr><td><a href="<?php echo htvar($PHP_SELF)?>?stage=list_users"><strong>List Password File and User Groups File Contents</strong></a></td></tr>
	<tr><td>
	<a href="<?php echo htvar($PHP_SELF)?>?stage=renew_CA_confirm"><strong>Extend the CA Certificate Lifetime</strong></a><br>
	This process will backup the old CA certificate that is about to expire and create a new certificate with the same serial number and a new validity duration.
	</td></tr>
	<!-- 
	
	<tr><td>
	<a href=""><strong>Import an existing CA and backup the old CA</strong></a><br>
	<-- The data encoding type, enctype, MUST be specified as below --/>
	<form enctype="multipart/form-data" action="<?php echo htvar($PHP_SELF)?>?stage=import_CA" method="POST">
    <-- MAX_FILE_SIZE must precede the file input field
    <input type="hidden" name="MAX_FILE_SIZE" value="30000" />  
    This process will backup the current CA storage folder and then reset it. The imported CA will be integrated into the application.<br> 
    Please attach either a PKCS#12 file (format .pfx or .p12) or the private and public keys for your root CA.<br>
    You will be asked for the CA password.
	<p>Root CA PKCS#12 File:
	<!-- Name of input element determines name in $_FILES array --/>
	<input type="file" name="CA_pkcs12" style="background-color: white" />
	</p>
	<p>Root CA Private Key:
	<input type="file" name="CA_privkey" style="background-color: white" />
	</p>
	<p>Root CA Public Certificate:
	<input type="file" name="CA_cert" style="background-color: white" />
	</p>
	<input class="btn" type="submit" value="Upload File(s)" />
	</form>
	</td></tr>
	-->
	</table>
	</div>
	<br><br>
	<?php
	printFooter();
}

?>
