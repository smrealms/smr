<?php declare(strict_types=1);

$container = create_container('skeleton.php', 'validate.php');

if (Request::get('action') == "resend") {
	$account->sendValidationEmail();
	$container['msg'] = '<span class="green">The validation code has been resent to your e-mail address!</span>';
	forward($container);
}

// Only skip validation check if we explicitly chose to validate later
if (Request::get('action') != "skip") {
	if ($account->getValidationCode() != Request::get('validation_code')) {
		$container['msg'] = '<span class="red">The validation code you entered is incorrect!</span>';
		forward($container);
	}

	$account->setValidated(true);
	$account->update();

	// delete the notification (when send)
	$db->query('DELETE FROM notification
				WHERE account_id = ' . $db->escapeNumber($account->getAccountID()) . '
				AND notification_type = \'validation_code\'');
}

$container = create_container('login_check_processing.php');
$container['CheckType'] = 'Announcements';
forward($container);
