<?php

function get_album_nick($album_id) {
	if ($album_id == 0)
		return 'System';

	return SmrAccount::getAccount($album_id)->getHofName();
}

$template->assign('PageTopic','Approve Album Entries');

$db->query('SELECT *
			FROM album
			WHERE approved = \'TBC\'
			ORDER BY last_changed
			LIMIT 1');

if (!$db->getNumRows()) {

	$PHP_OUTPUT.=('Nothing to approve!');
	return;

}

if ($db->nextRecord()) {

	$album_id = $db->getField('account_id');
	$location = stripslashes($db->getField('location'));
	$email = stripslashes($db->getField('email'));
	$website = stripslashes($db->getField('website'));
	$day = $db->getField('day');
	$month = $db->getField('month');
	$year = $db->getField('year');
	$other = nl2br(stripslashes($db->getField('other')));
	$last_changed = $db->getField('last_changed');
	$disabled = $db->getField('disabled');

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
		$PHP_OUTPUT.=('<img src="upload/'.$album_id.'">');
	else
		$PHP_OUTPUT.=('<img src="upload/0">');

	$PHP_OUTPUT.=('</td>');
	$PHP_OUTPUT.=('</tr>');

	if (empty($location))
		$location = 'N/A';
	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<td align="right" width="10%" class="bold">Location :</td><td>'.$location.'</td>');
	$PHP_OUTPUT.=('</tr>');

	if (empty($email))
		$email = 'N/A';
	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<td align="right" width="10%" class="bold">eMail :</td><td>'.$email.'</td>');
	$PHP_OUTPUT.=('</tr>');

	if (empty($website))
		$website = 'N/A';
	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<td align="right" width="10%" class="bold">Website :</td><td><a href="'.$website.'">'.$website.'</a></td>');
	$PHP_OUTPUT.=('</tr>');

	$PHP_OUTPUT.=('<tr>');
	if (!empty($day) && !empty($month) && !empty($year))
		$birthdate = $month.' / '.$day.' / '.$year;
	if (empty($birthdate) && !empty($year))
		$birthdate = 'Year '.$year;
	if (empty($birthdate))
		$birthdate = 'N/A';
	$PHP_OUTPUT.=('<td align="right" width="10%" class="bold">Birthdate :</td><td>'.$birthdate.'</td>');
	$PHP_OUTPUT.=('</tr>');

	if (empty($other))
		$other = 'N/A';
	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<td align="right" valign="top" width="10%" class="bold">Other&nbsp;Info :<br /><small>(AIM/ICQ)&nbsp;&nbsp;</small></td><td>'.$other.'</td>');
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
