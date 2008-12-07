<?php

function get_album_nick($album_id) {

	if ($album_id == 0)
		return 'System';

	$album = new SMR_DB();

	// get hof name
	$album->query('SELECT HoF_name
				   FROM account_has_stats
				   WHERE account_id = $album_id');
	if ($album->next_record())
		$nick = $album->f('HoF_name');

	// fall back to login name if it's empty or we havn't found one
	if (empty($nick)) {

		$album->query('SELECT login
					   FROM account
					   WHERE account_id = $album_id');
		if ($album->next_record())
			$nick = $album->f('login');

	}


	return $nick;

}

$smarty->assign('PageTopic','Approve Album Entries');

$db->query('SELECT *
			FROM album
			WHERE approved = \'TBC\'
			ORDER BY last_changed
			LIMIT 1');

if (!$db->nf()) {

	$PHP_OUTPUT.=('Nothing to approve!');
	return;

}

if ($db->next_record()) {

	$album_id = $db->f('account_id');
	$location = stripslashes($db->f('location'));
	$email = stripslashes($db->f('email'));
	$website = stripslashes($db->f('website'));
	$day = $db->f('day');
	$month = $db->f('month');
	$year = $db->f('year');
	$other = nl2br(stripslashes($db->f('other')));
	$last_changed = $db->f('last_changed');
	$disabled = $db->f('disabled');

	// get this user's nick
	$nick = get_album_nick($album_id);

	$PHP_OUTPUT.=('<table border="0" align="center" cellpadding="5" cellspacing="0">');
	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<td align="center" colspan="2">');
	$PHP_OUTPUT.=('<span style="font-size:150%;">'.$nick.'</span>');
	$PHP_OUTPUT.=('</td>');
	$PHP_OUTPUT.=('</tr>');
	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<td colspan="2" align="center" valign="middle">');

	if ($disabled == 'FALSE')
		$PHP_OUTPUT.=('<img src="'.$URL.'/upload/'.$album_id.'">');
	else
		$PHP_OUTPUT.=('<img src="'.$URL.'/upload/0">');

	$PHP_OUTPUT.=('</td>');
	$PHP_OUTPUT.=('</tr>');

	if (empty($location))
		$location = 'N/A';
	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<td align="right" width="10%" style="font-weight:bold;">Location :</td><td>'.$location.'</td>');
	$PHP_OUTPUT.=('</tr>');

	if (empty($email))
		$email = 'N/A';
	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<td align="right" width="10%" style="font-weight:bold;">eMail :</td><td>'.$email.'</td>');
	$PHP_OUTPUT.=('</tr>');

	if (empty($website))
		$website = 'N/A';
	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<td align="right" width="10%" style="font-weight:bold;">Website :</td><td><a href="'.$webpage.'">'.$webpage.'</a></td>');
	$PHP_OUTPUT.=('</tr>');

	$PHP_OUTPUT.=('<tr>');
	if (!empty($day) && !empty($month) && !empty($year))
		$birthdate = $month.' / '.$day.' / '.$year;
	if (empty($birthdate) && !empty($year))
		$birthdate = 'Year '.$year;
	if (empty($birthdate))
		$birthdate = 'N/A';
	$PHP_OUTPUT.=('<td align="right" width="10%" style="font-weight:bold;">Birthdate :</td><td>'.$birthdate.'</td>');
	$PHP_OUTPUT.=('</tr>');

	if (empty($other))
		$other = 'N/A';
	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<td align="right" valign="top" width="10%" style="font-weight:bold;">Other&nbsp;Info :<br><small>(AIM/ICQ)&nbsp;&nbsp;</small></td><td>'.$other.'</td>');
	$PHP_OUTPUT.=('</tr>');
	$PHP_OUTPUT.=('</table>');

	// get the time that passed since the entry was last changed
	$time_passed = TIME - $last_changed;

	gmstrftime($time_passed);

	// how many days?
	$days = floor($time_passed / 86400);

	// subtract the days from our time left
	$time_passed -= ($days * 86400);

	// how many hours
	$hours = floor($time_passed / 3600);

	// subtract the hours from our time left
	$time_passed -= ($hours * 3600);

	// how many minutes
	$minutes = floor($time_passed / 60);

	// subtract the minutes from our time left
	$seconds = $time_passed - ($minutes * 60);

	$PHP_OUTPUT.=('<p>Waiting for approval for '.$days.' days, '.$hours.' hours, '.$minutes.' minutes and '.$seconds.' seconds.</p>');

	$container = create_container('album_approve_processing.php', '');
	$container['album_id'] = $album_id;
	$PHP_OUTPUT.=create_echo_form($container);

	$PHP_OUTPUT.=create_submit('Approve');
	$PHP_OUTPUT.=('&nbsp;&nbsp;&nbsp;');
	$PHP_OUTPUT.=create_submit('Don\'t Approve');

	$PHP_OUTPUT.=('</form>');

}

?>