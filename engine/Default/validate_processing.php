<?php

if (!empty($_REQUEST['validation_code'])) {
	// is this our validation code?
	if ($account->getValidationCode() != $_REQUEST['validation_code'])
		create_error('The validation code you entered is incorrect.');

	$account->setValidated(true);

	// delete the notification (when send)
	$db->query('DELETE FROM notification WHERE account_id = '.$account->getAccountID().' AND ' .
											'notification_type = \'validation_code\'');
}

forward(create_container('announcements_check.php'));

?>