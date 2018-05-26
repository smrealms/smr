<?php

$steps = $_REQUEST['steps'];
$subject = $_REQUEST['subject'];
$error_msg = $_REQUEST['error_msg'];
$description = $_REQUEST['description'];
$new_sub = '[Bug] '.$subject;

$message = 'Login: '.$account->getLogin().EOL.EOL.'-----------'.EOL.EOL.
	'Account ID: '.$account->getAccountID().EOL.EOL.'-----------'.EOL.EOL.
	'Description: '.$description.EOL.EOL.'-----------'.EOL.EOL.
	'Steps to repeat: '.$steps.EOL.EOL.'-----------'.EOL.EOL.
	'Error Message: '.$error_msg;

if(is_object($player)) {
	$player->sendMessageToBox(BOX_BUGS_REPORTED, $message);
}
else {
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

$container = array();
$container['url'] = 'skeleton.php';
if (SmrSession::$game_id > 0) {
	$container['body'] = 'current_sector.php';
}
else {
	$container['body'] = 'game_play.php';
}

forward($container);
