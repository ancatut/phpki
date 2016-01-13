<?php
include('../config.php');
include(STORE_DIR.'/config/config.php');
include('../include/my_functions.php');
include('../include/common.php');
include('../include/openssl_functions.php');
 
$openvpn_client_basecnf_text = file_get_contents($config['openvpn_client_cnf_dir']."/client_basecnf.conf");

printHeader('ca');

?>
<br><br>
<table class="menu" style="width:80%">	
	<tr>
	<th colspan="2"><h2>OpenVPN Client Configuration</h2></th>
	</tr>
</table>
<div style="text-align:center;">
<span><br>
This is the template used by PHPki to generate the OpenVPN config file.<br>
All users in possession of a valid certificate also need the config file to connect to our VPN.<br>
Edit if you know what you're doing.<br><br>
<form id="openvpn_edit_settings" action="" method="post" style="display:inline">
<textarea id="openvpn_client_basecnf_text" name="cnf_textarea" cols="40" rows="20" style="background:#DEE3EC" readonly><?php print htvar($openvpn_client_basecnf_text) ?></textarea>
<br><br>
<button class="btn" value="Edit" onclick="return hitEdit('openvpn_client_basecnf_text');">Edit</button>
<input type="submit" class="btn" name="submit" value="Save" >
</form>
</span>
<span>
<a href="<?php echo back_link(); ?>"><button class="btn">Go Back</button></a>
</span></div>

<?php
	printFooter();
?>

<script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>

<script type="text/javascript">
function hitEdit(id) {
	document.getElementById(id).readOnly = false;
	document.getElementById(id).style.background = "white";
	return false;
}
//function hitSave(id) {
//	document.getElementById(id).readOnly = true;
//	return true;	
//}
$(document).ready(function() {
	$('#openvpn_edit_settings').submit(function() { // catch the form's submit event
	    var request = $.ajax({ // create an AJAX call...
	        data: $(this).serialize(), // get the form data
	        type: $(this).attr('method'), // GET or POST
	        url: "ajaxPage.php", // the file to call	        
	        });
	    request.done(function() {	  
	    	//window.location.reload();  // Reload to prevent form resubmission
	        alert("File saved."); // Alert success
		});
		request.fail(function(jqXHR, textStatus, errorThrown) {
 			alert("An error has occurred, try again.\n Error: " + errorThrown); // Alert failure
		});
		$(document.activeElement).blur(); // unfocus all active elements
		document.getElementById("openvpn_client_basecnf_text").readOnly = true; // set textarea to readonly
		document.getElementById("openvpn_client_basecnf_text").style.background = "#DEE3EC";			 
	    return false; // cancel original event to prevent form submitting
	});
});
</script>