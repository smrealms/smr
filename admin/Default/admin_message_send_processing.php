<?php declare(strict_types=1);

const ALL_GAMES_ID = 20000;
$message = trim(Request::get('message'));
$expire = Request::getFloat('expire');
$game_id = $var['SendGameID'];
if ($game_id != ALL_GAMES_ID) {
	$account_id = Request::getInt('account_id');
}

if (Request::get('action') == 'Preview message') {
	$container = create_container('skeleton.php', 'admin_message_send.php');
	transfer('SendGameID');
	$container['preview'] = $message;
	$container['expire'] = $expire;
	if ($game_id != ALL_GAMES_ID) {
		$container['account_id'] = $account_id;
	}
	forward($container);
}

if ($expire < 0) {
	create_error('Expire time cannot be negative!');
}
// When expire==0, message will not expire
if ($expire > 0) {
	$expire = ($expire * 3600) + TIME;
}

$receivers = [];
if ($game_id != ALL_GAMES_ID) {
	if ($account_id == 0) {
		// Send to all players in the requested game
		$db->query('SELECT account_id FROM player WHERE game_id = ' . $db->escapeNumber($game_id));
		while ($db->nextRecord()) {
			$receivers[] = [$game_id, $db->getInt('account_id')];
		}
	} else {
		$receivers[] = [$game_id, $account_id];
	}
} else {
	//send to all players in games that haven't ended yet
	$db->query('SELECT game_id,account_id FROM player JOIN game USING(game_id) WHERE end_time > ' . $db->escapeNumber(TIME));
	while ($db->nextRecord()) {
		$receivers[] = [$db->getInt('game_id'), $db->getInt('account_id')];
	}
}
// Send the messages
foreach ($receivers as $receiver) {
	SmrPlayer::sendMessageFromAdmin($receiver[0], $receiver[1], $message, $expire);
}
$msg = '<span class="green">SUCCESS: </span>Your message has been sent.';

$container = create_container('skeleton.php', 'admin_tools.php');
$container['msg'] = $msg;
forward($container);
