<?php declare(strict_types=1);

if (Request::get('action') == "Resend Validation Code") {
	$account->changeEmail($account->getEmail());
} else {
	$account->changeEmail(Request::get('email'));
}
$account->update();
Page::create('skeleton.php', 'validate.php')->go();
