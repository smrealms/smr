<?

function send_html_mail($from_name, $from_email, $to_email, $subject, $body) {

	$headers  = 'From: '.$from_name.'<'.$from_email.'>'.EOL;
	$headers .= 'X-Sender: '.$from_email.EOL;
	$headers .= 'X-Mailer: PHP'.EOL; //mailer
	$headers .= 'X-Priority: 3'.EOL; //1 UrgentMessage, 3 Normal
	$headers .= 'Return-Path: '.$from_email.EOL;
	$headers .= 'Content-Type: text/html; charset=iso-8859-1'.EOL;

	$message = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">'.EOL;
	$message .= '<HTML><BODY>'.EOL;
	$message .= wordwrap($body, 72);
	$message .= '</BODY></HTML>';

	// send the mail
	mail($to_email, $subject, $message, $headers, '-f '.$from_email);

}

// get account_id from session
$account_id = $var['account_id'];

// check for each task
if ($var['task'] == 'reset_image') {

	$db->query('UPDATE album SET disabled = \'TRUE\' WHERE account_id = '.$account_id);

	$curr_time = time();

	$db->lock('album_has_comments');
	$db->query('SELECT MAX(comment_id) FROM album_has_comments WHERE album_id = '.$account_id);
	if ($db->next_record())
		$comment_id = $db->f('MAX(comment_id)') + 1;
	else
		$comment_id = 1;

	$db->query('INSERT INTO album_has_comments
				(album_id, comment_id, time, post_id, msg)
				VALUES ('.$account_id.', '.$comment_id.', '.$curr_time.', 0, '.$db->escape_string('<span style="color:lime;">*** Picture disabled by an admin</span>').')');
	$db->unlock();

	// get his email address and send the mail
	$db->query('SELECT email FROM account WHERE account_id = '.$account_id);
	if ($db->next_record())
		send_html_mail('SMR Photo Album', 'pics@smrealms.de', $db->f('email'), 'SMR Photo Album Notification', nl2br($email_txt));

} else if ($var['task'] == 'reset_location')
	$db->query('UPDATE album SET location = \'\' WHERE account_id = '.$account_id);
else if ($var['task'] == 'reset_email')
	$db->query('UPDATE album SET email = \'\' WHERE account_id ='. $account_id);
else if ($var['task'] == 'reset_website')
	$db->query('UPDATE album SET website = \'\' WHERE account_id = '.$account_id);
else if ($var['task'] == 'reset_birthdate')
	$db->query('UPDATE album SET day = 0, month = 0, year = 0 WHERE account_id = '.$account_id);
else if ($var['task'] == 'reset_other')
	$db->query('UPDATE album SET other = \'\' WHERE account_id = '.$account_id);
else if ($var['task'] == 'delete_comment') {
	$comment_ids = $_REQUEST['comment_ids'];
	// we just ignore if nothing was set
	foreach ($comment_ids as $comment_id) {

		if ($comment_id_list)
			$comment_id_list .= ', ';
		$comment_id_list .= $comment_id;

	}

	if ($comment_id_list) {

		$db->query('DELETE
					FROM album_has_comments
					WHERE album_id = '.$account_id.' AND
						  comment_id IN ('.$comment_id_list.')');

	}

} else
	create_error('No action chosen!');


$container = create_container('skeleton.php', 'album_moderate.php');
transfer('account_id');
forward($container);

?>