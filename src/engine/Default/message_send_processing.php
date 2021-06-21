<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();

$message = htmlentities(trim(Request::get('message')), ENT_COMPAT, 'utf-8');

if (Request::get('action') == 'Preview message') {
	$container = Page::create('skeleton.php');
	if (isset($var['alliance_id'])) {
		$container['body'] = 'alliance_broadcast.php';
	} else {
		$container['body'] = 'message_send.php';
	}
	$container->addVar('receiver');
	$container->addVar('alliance_id');
	$container['preview'] = $message;
	$container->go();
}

if (empty($message)) {
	create_error('You have to enter a message to send!');
}

if (isset($var['alliance_id'])) {
	$db = Smr\Database::getInstance();
	$dbResult = $db->read('SELECT account_id FROM player
				WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
				AND alliance_id = ' . $var['alliance_id'] . '
				AND account_id != ' . $db->escapeNumber($player->getAccountID())); //No limit in case they are over limit - ie NHA
	foreach ($dbResult->records() as $dbRecord) {
		$player->sendMessage($dbRecord->getInt('account_id'), MSG_ALLIANCE, $message, false);
	}
	$player->sendMessage($player->getAccountID(), MSG_ALLIANCE, $message, true, false);
} elseif (!empty($var['receiver'])) {
	$player->sendMessage($var['receiver'], MSG_PLAYER, $message);
} else {
	$player->sendGlobalMessage($message);
}

$container = Page::create('skeleton.php');
if (isset($var['alliance_id'])) {
	$container['body'] = 'alliance_roster.php';
	$container->addVar('alliance_id');
} else {
	$container['body'] = 'current_sector.php';
}
$container['msg'] = '<span class="green">SUCCESS: </span>Your message has been sent.';
$container->go();
