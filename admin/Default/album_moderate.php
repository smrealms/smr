<?

function get_album_nick($album_id) {

	if ($album_id == 0)
		return 'System';

	$album = new SMR_DB();

	// get hof name
	$album->query('SELECT HoF_name
				   FROM account_has_stats
				   WHERE account_id = '.$album_id);
	if ($album->next_record())
		$nick = $album->f('HoF_name');

	// fall back to login name if it's empty or we havn't found one
	if (empty($nick)) {

		$album->query('SELECT login
					   FROM account
					   WHERE account_id = '.$album_id);
		if ($album->next_record())
			$nick = $album->f('login');

	}


	return $nick;

}

// try to get it from session first
$account_id = $var['account_id'];

// if it's emtpy try to get from form
if (empty($account_id))
	$account_id = get_form_value('account_id');

// check if input is numeric
if (!is_numeric($account_id)) {

	$PHP_OUTPUT.=create_echo_error('Please enter an account ID, which has to be nummeric!');
	return;

}

// echo green topic
$PHP_OUTPUT.=create_link(create_container('skeleton.php', 'album_moderate.php'),
		   '<h1>MODERATE PHOTO ALBUM</h1>');

// check if the givin account really has an entry
if ($account_id > 0) {

	$db->query('SELECT * FROM album WHERE account_id = '.$account_id.' AND Approved = \'YES\'');
	if ($db->next_record()) {

		$location = stripslashes($db->f('location'));
		$email = stripslashes($db->f('email'));
		$website = stripslashes($db->f('website'));
		$day = $db->f('day');
		$month = $db->f('month');
		$year = $db->f('year');
		$other = nl2br(stripslashes($db->f('other')));

	} else {

		$account_id = 0;
		$error_msg = '<div align="center" style="color:red;font-weight:bold;">This User doesn\'t have an album entry or it needs to be approved first!</div>';

	}

}

// if we don't have an account id yet, ask for it (and echo error message if invalid number was entered)
if (empty($account_id)) {

	$PHP_OUTPUT.=('Enter the account id of the entry you whish to edit:');
	$PHP_OUTPUT.=create_echo_form(create_container('skeleton.php', 'album_moderate.php'));
	$PHP_OUTPUT.=('<input type="text" name="account_id" size="5" id="InputFields" style="text-align:center;">&nbsp;');
	$PHP_OUTPUT.=create_submit('Submit');
	$PHP_OUTPUT.=('</form>');
	$PHP_OUTPUT.=($error_msg);

} else {

	$container = create_container('album_moderate_processing.php', '');
	$container['account_id'] = $account_id;

	$PHP_OUTPUT.=('<table border="0" align="center" cellpadding="5" cellspacing="0">');
	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<td align="center" colspan="3">');
	$PHP_OUTPUT.=('<span style="font-size:150%;">'.$nick.'</span></td><td>&nbsp;</td>');
	$PHP_OUTPUT.=('</tr>');
	$PHP_OUTPUT.=('<tr>');

	$container['task'] = 'reset_image';
	$PHP_OUTPUT.=create_echo_form($container);
	$PHP_OUTPUT.=('<td align="center">');
	$PHP_OUTPUT.=create_submit('Disable');
	$PHP_OUTPUT.=('</td>');

	$default_email = 'Dear Photo Album User,\n'.EOL .
					 'You have received this email as notification that the picture you submitted to the Space Merchant Realms Photo Album has been temporarily disabled due to a Photo Album Rules violation.'.EOL .
					 'Please visit '.$URL.'/album.php or log into the SMR site to upload a new picture.'.EOL .
					 'Reply to this email when you have uploaded a new picture so we may re-enable your pic.'.EOL .
					 'Note: Please allow up to 48 hours for changes to occur.'.EOL .
					 'Thanks,\n'.EOL .
					 'Admin Team';

	$PHP_OUTPUT.=('<td colspan="2"><img src="'.$URL.'/upload/'.$account_id.'"></td>');
	$PHP_OUTPUT.=('<td style="font-size:75%;">You can edit the text that will be sent<br>to that user as an email if you reset his picture!<br><br>');
	$PHP_OUTPUT.=('<textarea name="email_txt" id="InputFields" style="width:300;height:200;">'.$default_email.'</textarea></td>');
	$PHP_OUTPUT.=('</form>');
	$PHP_OUTPUT.=('</tr>');

	$PHP_OUTPUT.=('<tr>');
	$container['task'] = 'reset_location';
	$PHP_OUTPUT.=create_echo_form($container);
	$PHP_OUTPUT.=('<td align="center">');
	$PHP_OUTPUT.=create_submit('Reset');
	$PHP_OUTPUT.=('</td>');
	$PHP_OUTPUT.=('</form>');
	if (empty($location))
		$location = 'N/A';
	$PHP_OUTPUT.=('<td align="right" width="10%" style="font-weight:bold;">Location :</td><td colspan="2">'.$location.'</td>');
	$PHP_OUTPUT.=('</tr>');

	$PHP_OUTPUT.=('<tr>');
	$container['task'] = 'reset_email';
	$PHP_OUTPUT.=create_echo_form($container);
	$PHP_OUTPUT.=('<td align="center">');
	$PHP_OUTPUT.=create_submit('Reset');
	$PHP_OUTPUT.=('</td>');
	$PHP_OUTPUT.=('</form>');
	if (empty($email))
		$email = 'N/A';
	$PHP_OUTPUT.=('<td align="right" width="10%" style="font-weight:bold;">eMail :</td><td colspan="2">'.$email.'</td>');
	$PHP_OUTPUT.=('</tr>');

	$PHP_OUTPUT.=('<tr>');
	$container['task'] = 'reset_website';
	$PHP_OUTPUT.=create_echo_form($container);
	$PHP_OUTPUT.=('<td align="center">');
	$PHP_OUTPUT.=create_submit('Reset');
	$PHP_OUTPUT.=('</td>');
	$PHP_OUTPUT.=('</form>');
	if (empty($website))
		$website = 'N/A';
	else
		$website = '<a href="'.$website.'" target="_new">'.$website.'</a>';
	$PHP_OUTPUT.=('<td align="right" width="10%" style="font-weight:bold;">Website :</td><td>'.$website.'</td>');
	$PHP_OUTPUT.=('</tr>');

	$PHP_OUTPUT.=('<tr>');
	if (!empty($day) && !empty($month) && !empty($year))
		$birthdate = $month .' / '.$day.' / '.$year;
	if (empty($birthdate) && !empty($year))
		$birthdate = 'Year '.$year;
	if (empty($birthdate))
		$birthdate = 'N/A';

	$container['task'] = 'reset_birthdate';
	$PHP_OUTPUT.=create_echo_form($container);
	$PHP_OUTPUT.=('<td align="center">');
	$PHP_OUTPUT.=create_submit('Reset');
	$PHP_OUTPUT.=('</td>');
	$PHP_OUTPUT.=('</form>');

	$PHP_OUTPUT.=('<td align="right" width="10%" style="font-weight:bold;">Birthdate :</td><td colspan="2">'.$birthdate.'</td>');
	$PHP_OUTPUT.=('</tr>');

	$PHP_OUTPUT.=('<tr>');
	$container['task'] = 'reset_other';
	$PHP_OUTPUT.=create_echo_form($container);
	$PHP_OUTPUT.=('<td align="center">');
	$PHP_OUTPUT.=create_submit('Reset');
	$PHP_OUTPUT.=('</td>');
	$PHP_OUTPUT.=('</form>');
	if (empty($other))
		$other = 'N/A';
	$PHP_OUTPUT.=('<td align="right" valign="top" width="10%" style="font-weight:bold;">Other&nbsp;Info :<br><small>(AIM/ICQ)&nbsp;&nbsp;</small></td><td colspan="2">'.$other.'</td>');
	$PHP_OUTPUT.=('</tr>');

	$container['task'] = 'delete_comment';
	$PHP_OUTPUT.=create_echo_form($container);
	$PHP_OUTPUT.=('<input type="hidden" name="task" value="delete_comment">');
	$PHP_OUTPUT.=('<tr><td>&nbsp;</td><td colspan="3"><u>Comments</u></td></tr>');

	$db->query('SELECT *
				FROM album_has_comments
				WHERE album_id = $account_id');
	while ($db->next_record()) {

		$comment_id	= $db->f('comment_id');
		$time		= $db->f('time');
		$postee		= get_album_nick($db->f('post_id'));
		$msg		= stripslashes($db->f('msg'));

		$PHP_OUTPUT.=('<tr><td align="center"><input type="checkbox" name="comment_ids[]" value="'.$comment_id.'"></td><td colspan="3"><span style="font-size:85%;">[' . date('Y/n/j g:i A', $time) . '] &lt;'.$postee.'&gt; '.$msg.'</span></td></tr>');

	}

	$PHP_OUTPUT.=('<tr><td align="center">');
	$PHP_OUTPUT.=create_submit('Delete');
	$PHP_OUTPUT.=('</td>');
	$PHP_OUTPUT.=('<td colspan="3">&nbsp;</td></tr>');
	$PHP_OUTPUT.=('</form>');

	$PHP_OUTPUT.=('</table>');

}

?>