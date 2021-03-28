<?php declare(strict_types=1);

$session = SmrSession::getInstance();

$receiver = Request::get('receiver');
$subject = Request::get('subject');
$msg = Request::get('msg');

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

$container = Page::create('skeleton.php');
if ($session->hasGame()) {
	$container['body'] = 'current_sector.php';
} else {
	$container['body'] = 'game_play.php';
}

$container->go();
