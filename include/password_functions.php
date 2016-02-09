<?php
/**
 * Function change password in htpasswd.
 * Original function found at http://stackoverflow.com/a/8786956.
 * Listing it here for later use:
 * 
 * Arguments:
 * $user    > User name we want to change password to.
 * $newpass > New password
 * $type    > Type of cryptogrphy: DES, SHA, MD5. 
 * $salt    > Option: Add your custom salt (hashing string). 
 *            Salt is applied to DES and MD5 and must be in range 0-9A-Za-z
 * $oldpass > Option: Add more security, user must known old password to change it. 
 *            This option is not supported for DES and MD5 without salt!!!
 * $path    > Path to .htaccess file which contain the password protection. 
 *            Path to password file is obtained from this .htaccess file. 
 * 
 * 
function changePass($user,$newpass,$type="SHA",$salt="",$oldpass="",$path=".htaccess") {
  switch ($type) {
    case "DES" :
    $salt = substr($salt,0,2);  //Salt must be 2 char range 0-9A-Za-z
    $newpass = crypt($newpass,$salt);
    if ($oldpass != null) $oldpass = crypt($oldpass,$salt);
    break;

    case "SHA" :
    $newpass = '{SHA}'.base64_encode(sha1($newpass, TRUE));
    if ($oldpass != null) $oldpass = '{SHA}'.base64_encode(sha1($oldpass, TRUE));
    break;

    case "MD5" :
    $salt = substr($salt,0,8);  //Salt must be max 8 char range 0-9A-Za-z
    $newpass = crypt_apr1_md5($newpass, $salt);
    if ($oldpass != null) $oldpass = crypt_apr1_md5($oldpass, $salt);
    break;

    default :
    return false;
    break;
  }

  $hta_arr = explode("\n", file_get_contents($path));

  foreach($hta_arr as $line) {
    $line = preg_replace('/\s+/','',$line); // remove spaces
    if ($line) {
      $line_arr = explode('"', $line);
      if (strcmp($line_arr[0],"AuthUserFile") == 0) {
        $path_htaccess = $line_arr[1];
      }   
    }
  }  
  $htp_arr = explode("\n", file_get_contents($path_htaccess));

  $new_file = "";
  foreach($htp_arr as $line) {
    $line = preg_replace('/\s+/','',$line); // remove spaces
    if ($line) {
      list($usr, $pass) = explode(":", $line, 2);
      if (strcmp($user,$usr) == 0) {
        if ($oldpass != null) {
          if ($oldpass == $pass) {
            $new_file .= $user.':'.$newpass."\n";
          } else {
            return false;
          }
        } else {
          $new_file .= $user.':'.$newpass."\n";
        }
      } else {
        $new_file .= $user.':'.$pass."\n";
      }   
    }
  } 
  $f=fopen($path_htaccess,"w") or die("couldn't open the file");
  fwrite($f,$new_file);
  fclose($f);
  return true;
}
 *
 */

/**
 * Outputs a hash of the plain password.
 *
 */
function hashPass($newpass,$type="SHA",$salt="") {
	switch ($type) {
		case "DES" :
			$salt = substr($salt,0,2);  //Salt must be 2 char range 0-9A-Za-z
			$newpass = crypt($newpass,$salt);
			return $newpass;
			break;

		case "SHA" :
			$newpass = '{SHA}'.base64_encode(sha1($newpass, TRUE));
			return $newpass;
			break;

		case "APR1_MD5" :
			$salt = substr($salt,0,8);  //Salt must be max 8 char range 0-9A-Za-z
			$newpass = crypt_apr1_md5($newpass, $salt);
			return $newpass;
			break;

		default :
			return false;
			break;
	}
}
/**
 * Function for generating Apache-like MD5.
 * Found at http://stackoverflow.com/a/8786956
 */
function crypt_apr1_md5($plainpasswd,$salt=null) {
	$tmp = "";
	if ($salt == null) $salt = substr(str_shuffle("abcdefghijklmnopqrstuvwxyz0123456789"), 0, 8);
	$len = strlen($plainpasswd);
	$text = $plainpasswd.'$apr1$'.$salt;
	$bin = pack("H32", md5($plainpasswd.$salt.$plainpasswd));
	for($i = $len; $i > 0; $i -= 16) { $text .= substr($bin, 0, min(16, $i)); }
	for($i = $len; $i > 0; $i >>= 1) { $text .= ($i & 1) ? chr(0) : $plainpasswd{0}; }
	$bin = pack("H32", md5($text));
	for($i = 0; $i < 1000; $i++) {
		$new = ($i & 1) ? $plainpasswd : $bin;
		if ($i % 3) $new .= $salt;
		if ($i % 7) $new .= $plainpasswd;
		$new .= ($i & 1) ? $bin : $plainpasswd;
		$bin = pack("H32", md5($new));
	}
	for ($i = 0; $i < 5; $i++) {
		$k = $i + 6;
		$j = $i + 12;
		if ($j == 16) $j = 5;
		$tmp = $bin[$i].$bin[$k].$bin[$j].$tmp;
	}
	$tmp = chr(0).chr(0).$bin[11].$tmp;
	$tmp = strtr(strrev(substr(base64_encode($tmp), 2)),
			"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/",
			"./0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz");
	return "$"."apr1"."$".$salt."$".$tmp;
}

/**
 * This function checks the contents of the htgroups file and updates by adding/removing
 * the given user. Commented out are the updates to the $PHPki_admins array and config.php.
 */
function update_groupfile($user_id, $user_group, $action) {
	global $config;

	try {
		$groups_file = $config['groups_file'];
		$groups_file_contents = file_get_contents($groups_file);
		$contents_array = array_filter(explode("\n", $groups_file_contents), 'strlen');
	
		# Extract non-empty lines from file without line ending
		$admins_line = preg_grep('/^admin:\s.*?/', $contents_array);
		$cert_managers_line = preg_grep('/^cert-manager:\s.*?/', $contents_array);
		#$regular_users_line = preg_grep('/^regular-user:\s.*?/', $contents_array);
	
		# Preg_grep maintains key values from original array, so we can't do $admins_line[0]
		foreach($admins_line as $match)
			$admins = array_filter(explode(" ", substr($match, strpos($match, ": ") + 2)), 'strlen');
			foreach($cert_managers_line as $match)
				$cert_managers = array_filter(explode(" ", substr($match, strpos($match, ": ") + 2)), 'strlen');
				#foreach($regular_users_line as $match)
				#	$regular_users = array_filter(explode(" ", substr($match, strpos($match, ": ") + 2)));
				if ($action == "add_user") {
					if ($user_group == "admin") {
						$admins[] = $user_id;
						$cert_managers = array_diff($cert_managers, array($user_id));
						#	$regular_users = array_diff($regular_users, array($user_id));
					}
					else if ($user_group == "cert-manager") {
						$admins = array_diff($admins, array($user_id));
						$cert_managers[] = $user_id;
						#	$regular_users = array_diff($regular_users, array($user_id));
					}
				}
				else if ($action == "del_user")
				{
					$admins = array_diff($admins, array($user_id));
					$cert_managers = array_diff($cert_managers, array($user_id));
				}
				#else if ($user_group == "regular-user") {
				#	$admins = array_diff($admins, array($user_id));
				#	$cert_managers = array_diff($cert_managers, array($user_id));
				#	$regular_users[] = $user_id;
				#}
	
				$admins = array_unique($admins);
				sort($admins);
				$cert_managers = array_unique($cert_managers);
				sort($cert_managers);
				#$regular_users = array_unique($regular_users);
				#sort($regular_users);
	
				#$PHPki_admins = array_map(
				#	'md5_for_config',
				#	array_merge($admins, $cert_managers)
				#);
					
				#$data = file($config['store_dir']."/config/config.php"); // reads an array of lines
	
				#$matches = preg_grep('/^\$PHPki_admins.*$/', $data);
				#$matches_string = implode(', ', $PHPki_admins);
				#$ret = preg_match('/^\$PHPki_admins.*$/', $data);
				#$data = preg_replace('/^\$PHPki_admins.*$/', "\$PHPki_admins = Array(".$matches_string.");", $data);
	
				#file_put_contents($config['store_dir']."/config/config.php", implode('', $data));
	
				unset($groups_file_contents);
				$groups_file_contents = "admin: ".implode(' ', $admins)."\n";
				$groups_file_contents .= "cert-manager: ".implode(' ', $cert_managers)."\n";
				#$groups_file_contents .= "regular-user: ".implode(' ', $regular_users)."\n";
	
				file_put_contents($groups_file, $groups_file_contents);
				return true;
	}
			catch (Exception $e) {
				var_dump($e->getMessage());
				return false;
				}
}

/**
 * Updates the htpasswd file by adding/updating/deleting the user and encrypts password
 * according to the chosen digest.
 */
function update_htpasswd_file($user_id, $passwd, $passwd_file, $action="del_user", $digest="APR1_MD5") 
{
	try {
		$file_contents = file_get_contents($passwd_file);
	
		$contents_array = array_filter(explode("\n", $file_contents), 'strlen');
		$matched_lines = preg_grep("/^".$user_id.":.*?/", $contents_array);
		$contents_array = array_diff($contents_array, $matched_lines);
		if ($action == "del_user") {
			$contents_array = array_diff($contents_array, $matched_lines);
		}
		else if ($action == "add_user" || $action == "update_user") {
			$encrypted_passwd = hashPass($passwd, $digest);
			$contents_array[] = $user_id . ':' . $encrypted_passwd;
		}
		$contents = implode("\n", $contents_array);
		file_put_contents($passwd_file, $contents);
		return true;
	} catch (Exception $e) {
            var_dump($e->getMessage());
            return false;
        }       
}
?>