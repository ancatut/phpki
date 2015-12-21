<?php

include('../config.php');
include(STORE_DIR.'/config/config.php');
include('../include/my_functions.php');
include('../include/common.php');
include('../include/openssl_functions.php');

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
	<form action="<?php echo $PHP_SELF?>" method="post">
	<input class="btn" type="submit" name="submit" value="Back to Menu">
	</form>
	<?php
	printFooter(false);
	break;

case 'add_user_form':
	printHeader('admin');
	?>
	<body onLoad="self.focus();document.form.user_id.focus()">
	<form action="<?php echo $PHP_SELF?>" method="post" name="form">
	<table>
	<tr><th colspan="2"><h3>Add User or Update Existing User's Password or User Group</h3></th></tr>
	<tr><td>User ID</td><td><input type="text" name="user_id" value="<?php echo htvar($user_id)?>" maxlength=15 size=15></td></tr>
	<tr>
		<td>User Group (if the user already exists, it will update to this value)</td>
		<td>
		<select class="inputbox" name="user_group">
			<option value="admin">admin</option>
			<option value="cert-manager">cert-manager</option>
			<option value="regular-user">regular-user</option>
			</select>
		</td>
	</tr>
	<tr><td>Password (min. 8 characters long) (updates to the new value if user already exists)</td><td><input type="password" name="passwd" value='' size="20"></td></tr>
	<tr><td>Verify Password </td><td><input type="password" name="passwdv" value='' size="20"></td></tr>
	</table>
	<input type="hidden" name="stage" value="add_user">
	<input class="btn" type="submit" name="submit" value='Submit'>
	</form>
	<?php
	break;

case 'add_user':
	printHeader('admin');
	if (! $passwd || ! $passwdv || $passwd != $passwdv || strlen($passwd) < 8) {
		print '<div style="text-align:center"><h2 style="font-color:red">Missing or invalid password or password and password verification do not match.</h2></div>';

		?>
		<p><div style="text-align: center">
		<form action="<?php echo $PHP_SELF?>" method="post">
		<input type="hidden" name="stage" value="add_user_form">
		<input type="hidden" name="user_id" value="<?php echo htvar($user_id)?>">
		<input class="btn" type="submit" name="submit" value="Back">
		</form></div>
		<?php
	}
	else if (preg_match('/[^a-zA-Z0-9._]/', $user_id)) 
	{
		print '<div style="text-align:center"><h2 style="font-color:red">Invalid characters in username.</h2></div>';
	}
	else 
	{		
		echo "Checking if user is in $groups_file under $user_group, otherwise adding them...<br><br>";
		
		$groups_file = $config['groups_file'];
		$groups_file_contents = file_get_contents($groups_file);
		$contents_array = array_filter(explode("\n", $groups_file_contents));
	
		# Extract non-empty lines from file without line ending			
		$admins_line = preg_grep('/^admin:\s.*?/', $contents_array);	
		$cert_managers_line = preg_grep('/^cert-manager:\s.*?/', $contents_array);
		$regular_users_line = preg_grep('/^regular-user:\s.*?/', $contents_array);
		
		# Preg_grep maintains key values from original array, so we can't do $admins_line[0]
		foreach($admins_line as $match)
			$admins = array_filter(explode(" ", substr($match, strpos($match, ": ") + 2)));
		foreach($cert_managers_line as $match)
			$cert_managers = array_filter(explode(" ", substr($match, strpos($match, ": ") + 2)));
		foreach($regular_users_line as $match)
			$regular_users = array_filter(explode(" ", substr($match, strpos($match, ": ") + 2)));
		
		if ($user_group == "admin") {
			$admins[] = $user_id;
			$cert_managers = array_diff($cert_managers, array($user_id));
			$regular_users = array_diff($regular_users, array($user_id));
		}
		else if ($user_group == "cert-manager") {
			$admins = array_diff($admins, array($user_id));
			$cert_managers[] = $user_id;
			$regular_users = array_diff($regular_users, array($user_id));
		}
		else if ($user_group == "regular-user") {
			$admins = array_diff($admins, array($user_id));
			$cert_managers = array_diff($cert_managers, array($user_id));
			$regular_users[] = $user_id;
		}
			
		$admins = array_unique($admins);
		sort($admins);
		$cert_managers = array_unique($cert_managers);
		sort($cert_managers);
		$regular_users = array_unique($regular_users);
		sort($regular_users);
		
		$PHPki_admins = array_map(function($val) {
			return "md5('".$val."')";
			}, array_merge($admins, $cert_managers));
				
		$data = file($config['store_dir']."/config/config.php"); // reads an array of lines
		
		$matches = preg_grep('/^\$PHPki_admins.*$/', $data);
		$matches_string = implode(', ', $PHPki_admins);
		$ret = preg_match('/^\$PHPki_admins.*$/', $data);
		$data = preg_replace('/^\$PHPki_admins.*$/', "\$PHPki_admins = Array(".$matches_string.");\n", $data);

		file_put_contents($config['store_dir']."/config/config.php", implode('', $data));
		
		unset($groups_file_contents);			
		$groups_file_contents .= "admin: ".implode(' ', $admins)."\n";
		$groups_file_contents .= "cert-manager: ".implode(' ', $cert_managers)."\n";
		$groups_file_contents .= "regular-user: ".implode(' ', $regular_users)."\n";
		
		file_put_contents($groups_file, $groups_file_contents);
		
		$pwdfile = escapeshellarg($config['passwd_file']);
		$user_id = escapeshellarg($user_id);
		$passwd  = escapeshellarg($passwd);
		
		print 'Writing user password. Results of htpasswd command:<br>';
		system("htpasswd -bm $pwdfile $user_id $passwd 2>&1");
		print "<br><br>";
		
		print "Contents of groups file:<pre>";
		print file_get_contents($groups_file);
		print "</pre>";
		?>
		<p>
		<form action="<?php echo $PHP_SELF?>" method="post">
		<input class="btn" type="submit" name="submit" value="Back to Menu">
		</form>
		<?php
	}
	printFooter();
	break;

case 'del_user_form':
	printHeader('admin');
	?>
	<body onLoad="self.focus();document.form.login.focus()">
	<form action="<?php echo $PHP_SELF?>" method="post" name="form">
	<table>	
	<tr><th colspan="2"><h3>Remove User</h3></th></tr>
	<tr><td>User ID</td><td><input type="text" name="user_id" value="<?php echo htvar($user_id)?>" maxlength="15" size="15"></td></tr>
	</table>
	<input type="hidden" name="stage" value="del_user">
	<input class="btn" type="submit" name="submit" value='Submit'>
	</form>
	<?php
	printFooter();
	break;
case 'del_user':
	printHeader('admin');
	
	$groups_file = $config['groups_file'];
	$groups_file_contents = file_get_contents($groups_file);
	$groups_file_contents = preg_replace("/\s".$user_id."/", "", $groups_file_contents);
	file_put_contents($groups_file, $groups_file_contents);
	print "Removing user from groups file.<br><br>";
	$pwdfile = escapeshellarg($config['passwd_file']);
	$user_id = escapeshellarg($user_id);

	print 'Results of htpasswd command:<br>';
	system("htpasswd -D $pwdfile $user_id 2>&1");
	?>
	<p>
	<form action="<?php echo $PHP_SELF?>" method="post">
	<input class="btn" type="submit" name="submit" value="Back to Menu">
	</form>
	<?php
	printFooter();
	break;

/*
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
			<form action="<?php echo $PHP_SELF?>?stage=import_CA_confirm" method="POST">
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
	<form action="<?php print $PHP_SELF?>?stage=renew_CA" method="POST">
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
	<table class="menu"><tr><th><h3>SYSADMIN MENU</h3></th></tr>
	<tr><td><a href="<?php echo $PHP_SELF?>?stage=add_user_form"><strong>Add User or Update Existing User's Password or Group</strong></a></td></tr>
	<tr><td><a href="<?php echo $PHP_SELF?>?stage=del_user_form"><strong>Remove User</strong></a></td></tr>
	<tr><td><a href="<?php echo $PHP_SELF?>?stage=list_users"><strong>List Password File and User Groups File Contents</strong></a></td></tr>
	<tr><td>
	<a href="<?php echo $PHP_SELF?>?stage=renew_CA_confirm"><strong>Extend the CA Certificate Lifetime</strong></a><br>
	This process will backup the old CA certificate that is about to expire and create a new certificate with the same serial number and a new validity duration.
	</td></tr>
	<!-- 
	
	<tr><td>
	<a href=""><strong>Import an existing CA and backup the old CA</strong></a><br>
	<-- The data encoding type, enctype, MUST be specified as below --/>
	<form enctype="multipart/form-data" action="<?php echo $PHP_SELF?>?stage=import_CA" method="POST">
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
