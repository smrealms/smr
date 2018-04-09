<?php

//we are closing
$action = $_REQUEST['action'];
if ($action == 'Close') {
	$db->query('REPLACE INTO game_disable (reason) VALUES (' . $db->escape_string($_REQUEST['close_reason'], true) . ');');
	$db->query('DELETE FROM active_session;');
}
elseif ($action == 'Open') {
	$db->query('DELETE FROM game_disable;');
}

forward(create_container('skeleton.php', 'admin_tools.php'));
