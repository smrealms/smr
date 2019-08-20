<?php declare(strict_types=1);
$message = trim($_REQUEST['message']);
if ($_REQUEST['action'] == 'Preview announcement') {
	$container = create_container('skeleton.php', 'announcement_create.php');
	$container['preview'] = $message;
	forward($container);
}

// put the msg into the database
$db->query('INSERT INTO announcement (time, admin_id, msg) VALUES(' . $db->escapeNumber(TIME) . ', ' . $db->escapeNumber($account->getAccountID()) . ', ' . $db->escapeString($message) . ')');

forward(create_container('skeleton.php', 'admin_tools.php'));
