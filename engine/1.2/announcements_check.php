<?php
$db = new SmrMySqlDatabase();
$db->query("SELECT * FROM account, announcement " .
		   "WHERE account_id = ".SmrSession::$old_account_id." AND " .
				 "last_login < time");

$container = array();

// do we have announcements?
if ($db->nf() != 0) {

	$container["url"] = "skeleton.php";
	$container["body"] = "announcements.php";

} else
	$container["url"] = "logged_in.php";

forward($container);

?>