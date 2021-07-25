<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$account = $session->getAccount();

$steps = Smr\Request::get('steps');
$subject = Smr\Request::get('subject');
$error_msg = Smr\Request::get('error_msg');
$description = Smr\Request::get('description');

$delim = EOL . EOL . '-----------' . EOL . EOL;
$message = 'Login: ' . $account->getLogin() . EOL .
	'Account ID: ' . $account->getAccountID() . $delim .
	'Subject: ' . $subject . $delim .
	'Description: ' . $description . $delim .
	'Steps to repeat: ' . $steps . $delim .
	'Error Message: ' . $error_msg;

if ($session->hasGame()) {
	$player = $session->getPlayer();
	$player->sendMessageToBox(BOX_BUGS_REPORTED, $message);
} else {
	$account->sendMessageToBox(BOX_BUGS_REPORTED, $message);
}

// Send report to e-mail so that we have a permanent record
if (!empty(BUG_REPORT_TO_ADDRESSES)) {
	$mail = setupMailer();
	$mail->Subject = PAGE_PREFIX . 'Player Bug Report';
	$mail->setFrom('bugs@smrealms.de');
	$mail->Body = $message;
	foreach (BUG_REPORT_TO_ADDRESSES as $toAddress) {
		$mail->addAddress($toAddress);
	}
	$mail->send();
}

$container = Page::create('skeleton.php');
$container['msg'] = '<span class="admin">ADMIN</span>: Bug report submitted. Thank you for helping to improve the game!';
if ($session->hasGame()) {
	$container['body'] = 'current_sector.php';
} else {
	$container['body'] = 'game_play.php';
}

$container->go();
