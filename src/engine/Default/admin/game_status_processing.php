<?php declare(strict_types=1);

$db = Smr\Database::getInstance();

$container = Page::create('skeleton.php', 'admin/admin_tools.php');

$action = Smr\Request::get('action');
if ($action == 'Close') {
	$reason = Smr\Request::get('close_reason');
	$db->write('REPLACE INTO game_disable (reason) VALUES (' . $db->escapeString($reason) . ');');
	$db->write('DELETE FROM active_session;');
	$container['msg'] = '<span class="green">SUCCESS: </span>You have closed the server. You will now be logged out!';
} elseif ($action == 'Open') {
	$db->write('DELETE FROM game_disable;');
	$container['msg'] = '<span class="green">SUCCESS: </span>You have opened the server.';
}

$container->go();
