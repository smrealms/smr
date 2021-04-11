<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$account = $session->getAccount();

if (Request::get('action') == "Resend Validation Code") {
	$account->changeEmail($account->getEmail());
} else {
	$account->changeEmail(Request::get('email'));
}
$account->update();
Page::create('skeleton.php', 'validate.php')->go();
