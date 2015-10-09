<?php

include('./config.php');
include(STORE_DIR.'/config/config.php');
include('./include/common.php');
include('./include/my_functions.php');
include('./include/openssl_functions.php');

$stage        = gpvar('stage');
$submit       = gpvar('submit');
$search       = gpvar('search');
$serial       = gpvar('serial');
$show_valid   = gpvar('show_valid');
$show_revoked = gpvar('show_revoked');
$show_expired = gpvar('show_expired');

# Force stage back to search form if search string is empty.
if ($stage == "search" && ! $search) $stage = "";

# Force filter to (V)alid certs if no search status is selected.
if ( !($show_valid.$show_revoked.$show_expired) ) $show_valid = 'V';

switch ($stage):
case 'display':
	printHeader('about');

	print '
	<div style="text-align:center"><h2>Certificate Details</h2></div>
	<div style="text-align:center"><font color=#0000AA><h3>(#'.htvar($serial).')<br>'.htvar(CA_cert_cname($serial).' <'.CA_cert_email($serial).'>').'</h3></font></div>';

	if ($revoke_date = CAdb_is_revoked($serial))
	print '<div style="text-align:center"><font color="red"><h2>REVOKED '.htvar($revoke_date).'</h2></font></div>';

	print '<pre>'.htvar(CA_cert_text($serial)).'</pre>';
	break;

case 'download':
	$rec = CAdb_get_entry($serial);
	upload($config['cert_dir']."/$serial.der", $rec[common_name]." (".$rec['email'].").cer", 'application/pkix-cert');
        break;

case 'search':
	printHeader('public');

	$db = CAdb_to_array("^[${show_valid}${show_revoked}${show_expired}].*$search");

	print '<body onLoad="self.focus();document.form.submit.focus()">';
	if (sizeof($db) == 0) {
		?>
		<div style="text-align:center">
		<h2>Nothing Found</h2>
		<form action="<?php print $PHP_SELF?>" method="post" name="form">
		<input type="hidden" name="search" value="<?php print htvar($search)?>">
		<input type="hidden" name="show_valid" value="<?php print htvar($show_valid)?>">
		<input type="hidden" name="show_revoked" value="<?php print htvar($show_revoked)?>">
		<input type="hidden" name="show_expired" value="<?php print htvar($show_expired)?>">
		<input type="submit" name="submit" value="Go Back">
		</form>
		</div>
		<?php
		printFooter();
		break;
	}
		?>
	

	<table>
	<tr><th colspan=9><h2>CERTIFICATE SEARCH RESULTS</h2></th></tr>
	<tr>
<?php
        $headings = array(
                'status'=>"Status", 'issued'=>"Issued", 'expires'=>"Expires",
                'common_name'=>"User's Name", 'email'=>"E-mail",
                'organization'=>"Organization", 'unit'=>"Department",
                'locality'=>"Locality", 'province'=>"State"
        );

        print '<tr>';
        foreach($headings as $field=>$head) {
                print '<th>'.htvar($head). '</th>';
        }
        print '</tr>';

	foreach($db as $rec) {
		$stcolor = array('Valid'=>'green', 'Revoked'=>'red', 'Expired'=>'orange');

		?>
		<tr style="font-size: 11px;">
		<td style="color: <?php print $stcolor[$rec['status']]?>; font-weight: bold"><?php print htvar($rec['status'])?></td>
		<td style="white-space: nowrap"><?php print htvar($rec['issued'])?></td>
		<td style="white-space: nowrap"><?php print htvar($rec['expires'])?></td>
		<td><?php print htvar($rec['common_name'])?></td>
		<td style="white-space: nowrap">
			<a href="mailto:<?php print htvar($rec['common_name']).' <'.htvar($rec['email']).'>' ?>">
			<?php print htvar($rec['email']) ?>
			</a>
		</td>
		<td><?php print htvar($rec['organization'])?></td>
		<td><?php print htvar($rec['unit'])?></td>
		<td><?php print htvar($rec['locality'])?></td>
		<td><?php print htvar($rec['province'])?></td>
		<td><a href="<?php print $PHP_SELF?>?stage=display&serial=<?php print htvar($rec['serial'])?>" target="_certdisp"><img src="images/display.png" alt="Display" title="Display the certificate in excruciating detail"></a>
		<?php
		if ($rec['status'] != 'Revoked') {
			?>
			<a href="<?php print $PHP_SELF ?>?stage=download&serial=<?php print htvar($rec['serial'])?>"><img src="images/download.png" alt="Download" title="Download the certificate so that you may send encrypted e-mail"></a>
			<?php
		}
		print '</td></tr>';
	}

	?>
	</table>

	<form action="<?php print $PHP_SELF ?>" method="post" name="form">
	<input type="submit" name="submit" value="Another Search">
	<input type="hidden" name="search" value="<?php print htvar($search) ?>">
	<input type="hidden" name="show_valid" value="<?php print htvar($show_valid) ?>">
	<input type="hidden" name="show_revoked" value="<?php print htvar($show_revoked) ?>">
	<input type="hidden" name="show_expired" value="<?php print htvar($show_expired) ?>">
	</form>
	<?php

	printFooter();
	break;

default:
	printHeader('public');

	?>
	<body onLoad="self.focus();document.search.search.focus()">
	<div style="text-align:center"><h2>Certificate Search</h2>
	<form action="<?php print $PHP_SELF?>" method="post" name="search">
	<input type="text" name="search" value="<?php print htvar($search)?>" maxlength="60" size="40">
	<input type="submit" name="submit" value="Find It!"><br>
	<input type="checkbox" name="show_valid" value="V" <?php print ($show_valid?'checked':'')?>>Valid
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name="show_revoked" value="R" <?php print ($show_revoked?'checked':'')?>>Revoked
	&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name="show_expired" value="E" <?php print ($show_expired?'checked':'')?>>Expired
	<input type="hidden" name="stage" value="search">
	</form></div>

	<br><br>
	<?php
	printFooter();
endswitch;
?>
