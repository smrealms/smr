<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$account = $session->getAccount();

$container = Page::create('skeleton.php', 'validate.php');

if (Request::get('action') == "resend") {
	$account->sendValidationEmail();
	$container['msg'] = '<span class="green">The validation code has been resent to your e-mail address!</span>';
	$container->go();
}

// Only skip validation check if we explicitly chose to validate later
if (Request::get('action') != "skip") {
	if ($account->getValidationCode() != Request::get('validation_code')) {
		$container['msg'] = '<span class="red">The validation code you entered is incorrect!</span>';
		$container->go();
	}

	$account->setValidated(true);
	$account->update();

	// delete the notification (when send)
	$db = Smr\Database::getInstance();
	$db->write('DELETE FROM notification
				WHERE account_id = ' . $db->escapeNumber($account->getAccountID()) . '
				AND notification_type = \'validation_code\'');
}

$container = Page::create('login_check_processing.php');
$container['CheckType'] = 'Announcements';
$container->go();
