<?php declare(strict_types=1);

$var = Smr\Session::getInstance()->getCurrentVar();

const ALL_GAMES_ID = 20000;
$message = trim(Request::get('message'));
$expire = Request::getFloat('expire');
$game_id = $var['SendGameID'];
if ($game_id != ALL_GAMES_ID) {
	$account_id = Request::getInt('account_id');
}

if (Request::get('action') == 'Preview message') {
	$container = Page::create('skeleton.php', 'admin_message_send.php');
	$container->addVar('SendGameID');
	$container['preview'] = $message;
	$container['expire'] = $expire;
	if ($game_id != ALL_GAMES_ID) {
		$container['account_id'] = $account_id;
	}
	$container->go();
}

$expire = IRound($expire * 3600); // convert hours to seconds
// When expire==0, message will not expire
if ($expire > 0) {
	$expire += Smr\Epoch::time();
}

$db = Smr\Database::getInstance();

$receivers = [];
if ($game_id != ALL_GAMES_ID) {
	if ($account_id == 0) {
		// Send to all players in the requested game
		$dbResult = $db->read('SELECT account_id FROM player WHERE game_id = ' . $db->escapeNumber($game_id));
		foreach ($dbResult->records() as $dbRecord) {
			$receivers[] = [$game_id, $dbRecord->getInt('account_id')];
		}
	} else {
		$receivers[] = [$game_id, $account_id];
	}
} else {
	//send to all players in games that haven't ended yet
	$dbResult = $db->read('SELECT game_id,account_id FROM player JOIN game USING(game_id) WHERE end_time > ' . $db->escapeNumber(Smr\Epoch::time()));
	foreach ($dbResult->records() as $dbRecord) {
		$receivers[] = [$dbRecord->getInt('game_id'), $dbRecord->getInt('account_id')];
	}
}
// Send the messages
foreach ($receivers as $receiver) {
	SmrPlayer::sendMessageFromAdmin($receiver[0], $receiver[1], $message, $expire);
}
$msg = '<span class="green">SUCCESS: </span>Your message has been sent.';

$container = Page::create('skeleton.php', 'admin_tools.php');
$container['msg'] = $msg;
$container->go();
