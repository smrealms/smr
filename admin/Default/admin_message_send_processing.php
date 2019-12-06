<?php declare(strict_types=1);

$message = trim($_REQUEST['message']);
$expire = $_REQUEST['expire'];
if ($_REQUEST['action'] == 'Preview message') {
	$container = create_container('skeleton.php', 'admin_message_send.php');
	transfer('SendGameID');
	$container['preview'] = $message;
	$container['expire'] = $expire;
	forward($container);
}

$game_id = $var['SendGameID'];
if (isset($_REQUEST['account_id']) || $game_id == 20000) {
	if (!is_numeric($expire)) {
		create_error('Expire time must be numeric!');
	}
	if ($expire < 0) {
		create_error('Expire time cannot be negative!');
	}
	// When expire==0, message will not expire
	if ($expire > 0) {
		$expire = ($expire * 3600) + TIME;
	}

	if ($game_id != 20000) {
		SmrPlayer::sendMessageFromAdmin($game_id, $_REQUEST['account_id'], $message, $expire);
	} else {
		//send to all players in games that haven't ended yet
		$db->query('SELECT game_id,account_id FROM player JOIN game USING(game_id) WHERE end_time > ' . $db->escapeNumber(TIME));
		while ($db->nextRecord()) {
			SmrPlayer::sendMessageFromAdmin($db->getInt('game_id'), $db->getInt('account_id'), $message, $expire);
		}
	}
	$msg = '<span class="green">SUCCESS: </span>Your message has been sent.';
} else {
	$msg = '<span class="bold red">ERROR: </span>You must specify a player to message!';
}

$container = create_container('skeleton.php', 'admin_tools.php');
$container['msg'] = $msg;
forward($container);
