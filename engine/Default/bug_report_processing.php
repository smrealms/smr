<?php declare(strict_types=1);

$steps = Request::get('steps');
$subject = Request::get('subject');
$error_msg = Request::get('error_msg');
$description = Request::get('description');

$delim = EOL . EOL . '-----------' . EOL . EOL;
$message = 'Login: ' . $account->getLogin() . EOL .
	'Account ID: ' . $account->getAccountID() . $delim .
	'Subject: ' . $subject . $delim .
	'Description: ' . $description . $delim .
	'Steps to repeat: ' . $steps . $delim .
	'Error Message: ' . $error_msg;

if (is_object($player)) {
	$player->sendMessageToBox(BOX_BUGS_REPORTED, $message);
} else {
	$account->sendMessageToBox(BOX_BUGS_REPORTED, $message);
}

// Send report to e-mail so that we have a permanent record
if (!empty(BUG_REPORT_TO_ADDRESSES)) {
	$mail = setupMailer();
	$mail->Subject = 'Player Bug Report';
	$mail->setFrom('bugs@smrealms.de');
	$mail->Body = $message;
	foreach (BUG_REPORT_TO_ADDRESSES as $toAddress) {
		$mail->addAddress($toAddress);
	}
	$mail->send();
}

$container = create_container('skeleton.php');
if (SmrSession::hasGame()) {
	$container['body'] = 'current_sector.php';
} else {
	$container['body'] = 'game_play.php';
}

forward($container);
