<?php declare(strict_types=1);

// get account_id from session
$account_id = $var['account_id'];

// check for each task
if ($var['task'] == 'reset_image') {
	$email_txt = Request::get('email_txt');
	$db->query('UPDATE album SET disabled = \'TRUE\' WHERE account_id = ' . $db->escapeNumber($account_id));

	$db->lockTable('album_has_comments');
	$db->query('SELECT MAX(comment_id) FROM album_has_comments WHERE album_id = ' . $db->escapeNumber($account_id));
	if ($db->nextRecord()) {
		$comment_id = $db->getInt('MAX(comment_id)') + 1;
	} else {
		$comment_id = 1;
	}

	$db->query('INSERT INTO album_has_comments
				(album_id, comment_id, time, post_id, msg)
				VALUES ('.$db->escapeNumber($account_id) . ', ' . $db->escapeNumber($comment_id) . ', ' . $db->escapeNumber(TIME) . ', 0, ' . $db->escapeString('<span class="green">*** Picture disabled by an admin</span>') . ')');
	$db->unlock();

	// get his email address and send the mail
	$receiver = SmrAccount::getAccount($account_id);
	if (!empty($receiver->getEmail())) {
		$mail = setupMailer();
		$mail->Subject = 'SMR Photo Album Notification';
		$mail->setFrom('album@smrealms.de', 'SMR Photo Album');
		$mail->msgHTML(nl2br($email_txt));
		$mail->addAddress($receiver->getEmail(), $receiver->getHofName());
		$mail->send();
	}

} elseif ($var['task'] == 'reset_location') {
	$db->query('UPDATE album SET location = \'\' WHERE account_id = ' . $db->escapeNumber($account_id));
} elseif ($var['task'] == 'reset_email') {
	$db->query('UPDATE album SET email = \'\' WHERE account_id =' . $db->escapeNumber($account_id));
} elseif ($var['task'] == 'reset_website') {
	$db->query('UPDATE album SET website = \'\' WHERE account_id = ' . $db->escapeNumber($account_id));
} elseif ($var['task'] == 'reset_birthdate') {
	$db->query('UPDATE album SET day = 0, month = 0, year = 0 WHERE account_id = ' . $db->escapeNumber($account_id));
} elseif ($var['task'] == 'reset_other') {
	$db->query('UPDATE album SET other = \'\' WHERE account_id = ' . $db->escapeNumber($account_id));
} elseif ($var['task'] == 'delete_comment') {
	// we just ignore if nothing was set
	if (Request::has('comment_ids')) {
		$db->query('DELETE
					FROM album_has_comments
					WHERE album_id = '.$db->escapeNumber($account_id) . ' AND
						  comment_id IN ('.$db->escapeArray(Request::getIntArray('comment_ids')) . ')');
	}
} else {
	create_error('No action chosen!');
}

$container = create_container('skeleton.php', 'album_moderate.php');
transfer('account_id');
forward($container);
