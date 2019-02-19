<?php

$container = array();
$container['url'] = 'skeleton.php';
$container['body'] = 'message_blacklist.php';

if(!isset($_REQUEST['PlayerName']) && !isset($var['account_id'])) {
	$container['error'] = 1;
	forward($container);
}

if(isset($var['account_id'])) {
	$blacklisted_id = $var['account_id'];
}
else {
	$db = new SmrMySqlDatabase();

	$db->query('SELECT account_id FROM player WHERE player_name=' . $db->escapeString($_REQUEST['PlayerName']) . ' AND game_id=' . $db->escapeNumber($player->getGameID()) . ' LIMIT 1');

	if(!$db->nextRecord()) {
		$container['error'] = 1;
		forward($container);
	}
	$blacklisted_id = $db->getField('account_id');
}

$db->query('SELECT account_id FROM message_blacklist WHERE account_id=' . $db->escapeNumber($player->getAccountID()) . ' AND blacklisted_id=' . $db->escapeNumber($blacklisted_id) . ' AND game_id=' . $db->escapeNumber($player->getGameID()) . ' LIMIT 1');

if($db->nextRecord()) {
	$container['error'] = 2;
	forward($container);
}

$db->query('INSERT INTO message_blacklist (game_id,account_id,blacklisted_id) VALUES (' . $db->escapeNumber($player->getGameID()) . ',' . $db->escapeNumber($player->getAccountID()) . ',' . $db->escapeNumber($blacklisted_id) . ')');

$container['error'] = 3;
forward($container);
