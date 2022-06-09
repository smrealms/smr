<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$account = $session->getAccount();

$receiver = Smr\Request::get('receiver');
$subject = Smr\Request::get('subject');
$msg = Smr\Request::get('msg');

$mail = setupMailer();
$mail->Subject = PAGE_PREFIX . $subject;
$mail->setFrom('contact@smrealms.de');
$mail->addReplyTo($account->getEmail(), $account->getHofName());
$mail->Body =
	'Login:' . EOL . '------' . EOL . $account->getLogin() . EOL . EOL .
	'Account ID:' . EOL . '-----------' . EOL . $account->getAccountID() . EOL . EOL .
	'Message:' . EOL . '------------' . EOL . $msg;
$mail->addAddress($receiver);
$mail->send();

if ($session->hasGame()) {
	$body = 'current_sector.php';
} else {
	$body = 'game_play.php';
}
Page::create($body)->go();
