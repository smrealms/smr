<?php declare(strict_types=1);

$container = create_container('skeleton.php', 'admin_tools.php');

$action = $_REQUEST['action'];
if ($action == 'Close') {
	$reason = $_REQUEST['close_reason'];
	$db->query('REPLACE INTO game_disable (reason) VALUES (' . $db->escapeString($reason, true) . ');');
	$db->query('DELETE FROM active_session;');
	$container['msg'] = '<span class="green">SUCCESS: </span>You have closed the server. You will now be logged out!';
} elseif ($action == 'Open') {
	$db->query('DELETE FROM game_disable;');
	$container['msg'] = '<span class="green">SUCCESS: </span>You have opened the server.';
}

forward($container);
