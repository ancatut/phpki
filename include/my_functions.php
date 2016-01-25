<?php
 
define('__ROOT__', dirname(dirname(__FILE__))); # Fetches the root folder of phpki
require_once(__ROOT__.'/vendor/phpmailer/phpmailer/class.phpmailer.php');

# TODO: Whitelist of files to which redirection is exclusively allowed
$redirect_whitelist = array("about.php", "common.php", "config.php", "index.php", "help.php", "main.php", "manage_certs.php", "my_functions.php", "openssl_functions.php", "request_cert.php", "setup.php");

# TODO: Whitelist of commands allowed by exec()
$command_whitelist = array();

$PHP_SELF = $_SERVER['PHP_SELF'];

/**
 * Returns TRUE if browser is Internet Explorer.
 */
function isIE() {
	global $_SERVER;
	return strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE');
}

function isKonq() {
	global $_SERVER;
	return strstr($_SERVER['HTTP_USER_AGENT'], 'Konqueror');
}

function isMoz() {
	global $_SERVER;
	return strstr($_SERVER['HTTP_USER_AGENT'], 'Gecko');
}

/**
 * Force upload of specified file to browser.
 */
function upload($source, $destination, $content_type="application/octet-stream") {
	# Clean the output buffer to avoid including any JavaScript code in the downloaded file
	ob_clean();
#	if(!in_array($_GET["$source"],  $whitelist)) exit ;
#	if(!in_array($_GET["$destination"],  $whitelist)) exit ;
	header('Content-Description: File Transfer');
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 
	header("Expires: -1");
#	header("Cache-Control: no-store, no-cache, must-revalidate");
#	header("Cache-Control: post-check=0, pre-check=0", false);
#	header("Pragma: no-cache");
    header("Content-Type: $content_type");

	if (is_array($source)) {
		$fsize = 0;
		foreach ($source as $f) $fsize += filesize($f);
	}
	else {
		$fsize = filesize($source);
	}

	header("Content-length: " . $fsize);
    header("Content-Disposition: attachment; filename=\"" . $destination ."\"");
#   header("Content-Disposition: filename=\"" . $destination ."\"");

	if (is_array($source))
		foreach ($source as $f) $ret = readfile($f);
	else 
        	$ret=readfile($source);
	
	return $ret;
	
#        $fd=fopen($source,'r');
#        fpassthru($fd);
#        fclose($fd);
}

/**
 * Use PHPMailer to send an email with attachment.
 * This function only works if there's a mail server running on the same network as PHPki
 */
function send_email($from_addr, $from_alias, $to_addr, $subject, $bodytext, $file_to_attach="") {
	$email = new PHPMailer();
	$email->From      = htvar($from_addr);
	$email->FromName  = htvar($from_alias);
	$email->Subject   = htvar($subject);
	$email->Body      = htvar($bodytext);
	$email->AddAddress( htvar($to_addr) );
	
	#$file_to_attach = 'PATH_OF_YOUR_FILE_HERE';
	
	$email->AddAttachment( htvar($file_to_attach), basename(htvar($file_to_attach)) );
	try {
	return $email->Send(); # returns true/false
	}
	catch (phpmailerException $e) {
		echo "<script type='text/javascript'>alert($e->errorMessage())</script>";		
	} 	catch (Exception $e) {
		echo "<script type='text/javascript'>alert($e->getMessage())</script>";
	}
	
	return $email->Send();
	#$email->Send();
}

/**
 * Returns a value from the GET/POST global array referenced
 * by field name.  POST fields have precedence over GET fields.
 * Quoting/Slashes are stripped if magic quotes gpc is on.
 */
function gpvar($v) {
	global $_GET, $_POST;
    $x = "";
	if (isset($_GET[$v]))  $x = $_GET[$v];
	if (isset($_POST[$v])) $x = $_POST[$v];	
	if (get_magic_quotes_gpc()) $x = stripslashes($x);
	return $x;
}

/**
 * Sort a two multidimensional array by one of its columns
 */
function csort($array, $column, $ascdec=SORT_ASC){    
	
	if (sizeof($array) == 0) return $array;

	foreach ($array as $x) 
		$sortarr[] = $x[$column];	
	#usort($sortarr, "nameComparator");
	
	#Seems to work almost properly now
	array_multisort($sortarr, $ascdec, SORT_NATURAL|SORT_FLAG_CASE|SORT_REGULAR, $array);  
	#array_multisort($sortarr, $ascdec, SORT_REGULAR, $array);

	#print "Time is: ".date('D, d M Y H:i:s ', $_SERVER['REQUEST_TIME']).'<br>';
	#print implode(" . . . ", $sortarr).'<br>';
	#print "Alphabetic: ". (int)is_alpha("Hi")." ";
	
	return $array;
}

/**
 * Returns a value suitable for display in the browser.
 * Strips slashes if second argument is true.
 */
function htvar($v, $strip=false) {
	if ($strip) 
		return  htmlentities(stripslashes($v));
	else
		return  htmlentities($v);	
}

/**
 * Returns a value suitable for use as a shell argument.
 * Strips slashes if magic quotes is on, surrounds
 * provided strings with single-quotes and quotes any
 * other dangerous characters.
 */
function escshellarg($v, $strip=false) {
	if ($strip)
		return escapeshellarg(stripslashes($v));
	else
		return escapeshellarg($v);
}

/**
 * Similar to escshellarg(), but doesn't surround provided
 * string with single-quotes.
 */
function escshellcmd($v, $strip=false) {
	if ($strip)
		return escapeshellcmd(stripslashes($v));
	else
		return escapeshellarg($v);
}
	
/**
 * Recursively strips slashes from a string or array.
 */
function stripslashes_array(&$a) {
	if (is_array($a)) {
		foreach ($a as $k => $v) {
			my_stripslashes($a[$k]);
		}
	}
	else {
		$a = stripslashes($a);
	}
}

/**
 * Don't use this.
 */
function undo_magic_quotes(&$a) {
	if(get_magic_quotes_gpc()) {
		global $HTTP_POST_VARS, $HTTP_GET_VARS;

		foreach ($HTTP_POST_VARS as $k => $v) {
			stripslashes_array($HTTP_POST_VARS[$k]);
			global $$k;
			stripslashes_array($$k);
		}
		foreach ($HTTP_GET_VARS as $k => $v) {
			stripslashes_array($HTTP_GET_VARS[$k]);
			global $$k;
			stripslashes_array($$k);
		}
	}
}

/**
 * Returns TRUE if argument contains only alphabetic characters.
 */
function is_alpha($v) {
##	return (eregi('[^A-Z]',$v) ? false : true) ;
##	return (preg_match('/[^A-Z]'.'/i',$v,PCRE_CASELESS) ? false : true) ; # Replaced eregi() with preg_match()
	return (preg_match('/[^A-Z]/i',$v) ? false : true) ;
}

/**
 * Returns TRUE if argument contains only numeric characters.
 */
function is_num($v) {
	##return (eregi('[^0-9]',$v) ? false : true) ;
	return (preg_match('/[^0-9]/',$v) ? false : true) ; # Replaced eregi() with preg_match()
}

/**
 * Returns TRUE if argument contains only alphanumeric characters.
 */
function is_alnum($v) {
##	return (eregi('[^A-Z0-9]',$v) ? false : true) ;
	return (preg_match('/[^A-Z0-9]/i',$v) ? false : true) ; # Replaced eregi() with preg_match()
}

/**
 * Returns TRUE if argument is in proper e-mail address format.
 */
function is_email($v) {
##	return (eregi('^[^@ ]+\@[^@ ]+\.[A-Z]{2,4}$',$v) ? true : false);
	return (preg_match('/^[^@ ]+\@[^@ ]+\.[A-Z]{2,4}$'.'/i',$v) ? true : false); # Replaced eregi() with preg_match()
}

/**
* Checks regexp in every element of an array, returns TRUE as soon
* as a match is found.
*/
function preg_match_array($regexp, $arr) {

	foreach ($arr as $elem) {
##		if (eregi($regexp,$elem))
		if (! preg_match('/^\/.*\/$/', $regexp)) # if it doesn't begin and end with '/'
			$regexp = '/'.$regexp.'/'; # pad the $regexp with '/' to prepare for preg_match()
		if (preg_match($regexp.'i',$elem)) # Replaced eregi() with preg_match()
			return true;
	}
	return false;
}

/**
 * Reads entire file into a string
 * Same as file_get_contents in php >= 4.3.0
 */
function my_file_get_contents($f) {
	return implode('', file($f));
}

/**
 * Checks if the file name contains only numbers, letters
 * and the characters "-" "_" "." "@" and that the name 
 * is not longer than 250 characters.
 */
function check_uploaded_filename ($filename)
{
	if (preg_match("%^[-0-9A-Z@_\.]+$%i",$filename))
		if (mb_strlen($filename,"UTF-8"))			
			return (mb_strlen($filename,"UTF-8") < 250);
		else return false;
	else return false;
}

/** 
 * Returns the previous page the user was on if not the same as the current page, for navigation
 */
function back_link() {
	if (isset($_SERVER['HTTP_REFERER']) && ($_SERVER['HTTP_REFERER'] != $_SERVER['REQUEST_URI']))
		return $_SERVER['HTTP_REFERER'];
	else return "index.php";
}

/**
 * Clear PHP session
 */
function clear_session() {
	session_start();
	session_destroy();
	header("Location: ".__ROOT__."/index.php");
}

/**
 * Writes username, email and clear password into a given log file, using a specified separator.
 */
function log_password_entry($log_file, $username, $email, $passwd, $separator="\t") {
	$myfile = fopen($log_file, "ab");
	fwrite($myfile, $username.$separator.$email.$separator.$passwd."\n");
	fclose($myfile);
}

/**
 * This function checks the contents of the htgroups file and updates by adding/removing 
 * the given user. Commented out are the updates to the $PHPki_admins array and config.php.
 */
function update_groupfile($user_id, $user_group, $action) {
	global $config;
	
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
}
?>
