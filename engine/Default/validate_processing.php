<?php

// Only skip validation check if we explicitly chose to validate later
if ($_REQUEST['action'] != "I'll validate later.") {
	if ($account->getValidationCode() != $_REQUEST['validation_code']) {
		$container = create_container('skeleton.php', 'validate.php');
		$container['msg'] = '<span class="red">The validation code you entered is incorrect!</span>';
		forward($container);
	}

	$account->setValidated(true);

	// delete the notification (when send)
	$db->query('DELETE FROM notification
				WHERE account_id = ' . $db->escapeNumber($account->getAccountID()) . '
				AND notification_type = \'validation_code\'');
}
$container = create_container('login_check_processing.php');
$container['CheckType'] = 'Announcements';
forward($container);
