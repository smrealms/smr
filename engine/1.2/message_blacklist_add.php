<?php

$container = array();
$container['url'] = 'skeleton.php';
$container['body'] = 'message_blacklist.php';

if(!isset($_REQUEST['PlayerName'])) {
	$container['error'] = 1;	
	forward($container);
	exit;
}

$db = new SmrMySqlDatabase();

$db->query('SELECT account_id FROM player WHERE player_name=\'' . $db->escape_string($_REQUEST['PlayerName']) . '\' AND game_id=' . SmrSession::$game_id . ' LIMIT 1');

if(!$db->next_record()) {
	$container['error'] = 1;	
	forward($container);
	exit;
}

$blacklisted_id = $db->f('account_id');

$db->query('SELECT account_id FROM message_blacklist WHERE account_id=' . SmrSession::$old_account_id . ' AND blacklisted_id=' . $blacklisted_id . ' AND game_id=' . SmrSession::$game_id . ' LIMIT 1');

if($db->next_record()) {
	$container['error'] = 2;	
	forward($container);
	exit;
}

$db->query('INSERT INTO message_blacklist (game_id,account_id,blacklisted_id) VALUES (' . SmrSession::$game_id . ',' . SmrSession::$old_account_id . ',' . $blacklisted_id . ')');

$container['error'] = 3;	
forward($container);


?>
