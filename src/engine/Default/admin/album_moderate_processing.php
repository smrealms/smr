<?php declare(strict_types=1);

$db = Smr\Database::getInstance();
$var = Smr\Session::getInstance()->getCurrentVar();

// get account_id from session
$account_id = $var['account_id'];

// check for each task
if ($var['task'] == 'reset_image') {
	$email_txt = Smr\Request::get('email_txt');
	$db->write('UPDATE album SET disabled = \'TRUE\' WHERE account_id = ' . $db->escapeNumber($account_id));

	$db->lockTable('album_has_comments');
	$dbResult = $db->read('SELECT IFNULL(MAX(comment_id)+1, 0) as next_comment_id FROM album_has_comments WHERE album_id = ' . $db->escapeNumber($account_id));
	$comment_id = $dbResult->record()->getInt('next_comment_id');

	$db->insert('album_has_comments', [
		'album_id' => $db->escapeNumber($account_id),
		'comment_id' => $db->escapeNumber($comment_id),
		'time' => $db->escapeNumber(Smr\Epoch::time()),
		'post_id' => 0,
		'msg' => $db->escapeString('<span class="green">*** Picture disabled by an admin</span>'),
	]);
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
	$db->write('UPDATE album SET location = \'\' WHERE account_id = ' . $db->escapeNumber($account_id));
} elseif ($var['task'] == 'reset_email') {
	$db->write('UPDATE album SET email = \'\' WHERE account_id =' . $db->escapeNumber($account_id));
} elseif ($var['task'] == 'reset_website') {
	$db->write('UPDATE album SET website = \'\' WHERE account_id = ' . $db->escapeNumber($account_id));
} elseif ($var['task'] == 'reset_birthdate') {
	$db->write('UPDATE album SET day = 0, month = 0, year = 0 WHERE account_id = ' . $db->escapeNumber($account_id));
} elseif ($var['task'] == 'reset_other') {
	$db->write('UPDATE album SET other = \'\' WHERE account_id = ' . $db->escapeNumber($account_id));
} elseif ($var['task'] == 'delete_comment') {
	// we just ignore if nothing was set
	if (Smr\Request::has('comment_ids')) {
		$db->write('DELETE
					FROM album_has_comments
					WHERE album_id = ' . $db->escapeNumber($account_id) . ' AND
						  comment_id IN (' . $db->escapeArray(Smr\Request::getIntArray('comment_ids')) . ')');
	}
} else {
	create_error('No action chosen!');
}

$container = Page::create('skeleton.php', 'admin/album_moderate.php');
$container->addVar('account_id');
$container->go();
