<?php
$message = trim($_REQUEST['message']);
if($_REQUEST['action'] == 'Preview announcement') {
	$container = create_container('skeleton.php','announcement_create.php');
	$container['preview'] = $message;
	forward($container);
}

if (strlen($message) > 255) {
	create_error('No more than 255 characters per announcement!');
}

// put the msg into the database
$db->query('INSERT INTO announcement (time, admin_id, msg) VALUES('.$db->escapeNumber(TIME).', '.$db->escapeNumber(SmrSession::$account_id).', '.$db->escapeString($message).')');

forward(create_container('skeleton.php', 'admin_tools.php'))
