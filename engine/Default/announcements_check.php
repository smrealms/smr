<?php
$db = new SmrMySqlDatabase();
$db->query('SELECT 1 FROM account JOIN announcement ON last_login < time
			WHERE account_id = ' . $db->escapeNumber(SmrSession::$account_id) . '  LIMIT 1');
// do we have announcements?
if ($db->getNumRows() != 0) {
	$container = create_container('skeleton.php', 'announcements.php');
}
else {
	$container = create_container('logged_in.php');
}

forward($container);

?>