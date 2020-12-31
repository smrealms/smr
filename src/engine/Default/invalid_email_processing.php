<?php declare(strict_types=1);

if (Request::get('action') == "Resend Validation Code") {
	$account->changeEmail($account->getEmail());
} else {
	$account->changeEmail(Request::get('email'));
}
$account->update();
forward(create_container('skeleton.php', 'validate.php'));
