<?php declare(strict_types=1);

$message = htmlentities(trim(Request::get('message')), ENT_COMPAT, 'utf-8');

if (Request::get('action') == 'Preview message') {
	$container = create_container('skeleton.php');
	if (isset($var['alliance_id'])) {
		$container['body'] = 'alliance_broadcast.php';
	} else {
		$container['body'] = 'message_send.php';
	}
	transfer('receiver');
	transfer('alliance_id');
	$container['preview'] = $message;
	forward($container);
}

if (empty($message)) {
	create_error('You have to enter a message to send!');
}

if (isset($var['alliance_id'])) {
	$db->query('SELECT player_id FROM player
				WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
				AND alliance_id = ' . $var['alliance_id'] . '
				AND player_id != ' . $db->escapeNumber($player->getPlayerID())); //No limit in case they are over limit - ie NHA
	while ($db->nextRecord()) {
		$player->sendMessage($db->getInt('player_id'), MSG_ALLIANCE, $message, false);
	}
	$player->sendMessage($player->getPlayerID(), MSG_ALLIANCE, $message, true, false);
} elseif (!empty($var['receiver'])) {
	$player->sendMessage($var['receiver'], MSG_PLAYER, $message);
} else {
	$player->sendGlobalMessage($message);
}

$container = create_container('skeleton.php');
if (isset($var['alliance_id'])) {
	$container['body'] = 'alliance_roster.php';
	transfer('alliance_id');
} else {
	$container['body'] = 'current_sector.php';
}
$container['msg'] = '<span class="green">SUCCESS: </span>Your message has been sent.';
forward($container);
