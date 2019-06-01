<?php

$receiver = $_REQUEST['receiver'];
$subject = $_REQUEST['subject'];
$msg = $_REQUEST['msg'];

$mail = setupMailer();
$mail->Subject = $subject;
$mail->setFrom($account->getEmail(), $account->getHofName());
$mail->Body =
	'Login:' . EOL . '------' . EOL . $account->getLogin() . EOL . EOL .
	'Account ID:' . EOL . '-----------' . EOL . $account->getAccountID() . EOL . EOL .
	'Message:' . EOL . '------------' . EOL . $msg;
$mail->addAddress($receiver);
$mail->send();

$container = create_container('skeleton.php');
if (SmrSession::hasGame()) {
	$container['body'] = 'current_sector.php';
} else {
	$container['body'] = 'game_play.php';
}

forward($container);
