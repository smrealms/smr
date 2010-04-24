<?php

if (!empty($_REQUEST['validation_code'])) {

	// is this our validation code?
	if ($account->validation_code != $_REQUEST['validation_code'])
		create_error("The validation code you entered is incorrect.");

	$account->validated = "TRUE";
	$account->update();

	// delete the notification (when send)
	$db->query("DELETE FROM notification WHERE account_id = ".SmrSession::$old_account_id." AND " .
											  "notification_type = 'validation_code'");

}

forward(create_container("announcements_check.php", ""));

?>