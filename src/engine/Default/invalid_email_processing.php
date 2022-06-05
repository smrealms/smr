<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$account = $session->getAccount();

if (Smr\Request::get('action') == 'Resend Validation Code') {
	$account->changeEmail($account->getEmail());
} else {
	$account->changeEmail(Smr\Request::get('email'));
}
$account->update();
Page::create('validate.php')->go();
