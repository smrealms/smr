<?php declare(strict_types=1);

$container = create_container('skeleton.php', 'admin_tools.php');

$action = Request::get('action');
if ($action == 'Close') {
	$reason = Request::get('close_reason');
	$db->query('REPLACE INTO game_disable (reason) VALUES (' . $db->escapeString($reason) . ');');
	$db->query('DELETE FROM active_session;');
	$container['msg'] = '<span class="green">SUCCESS: </span>You have closed the server. You will now be logged out!';
} elseif ($action == 'Open') {
	$db->query('DELETE FROM game_disable;');
	$container['msg'] = '<span class="green">SUCCESS: </span>You have opened the server.';
}

forward($container);
