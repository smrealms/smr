<?php declare(strict_types=1);

use Smr\Database;
use Smr\Request;

$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();

$message = htmlentities(Request::get('message'), ENT_COMPAT, 'utf-8');

if (Request::get('action') == 'Preview message') {
	if (isset($var['alliance_id'])) {
		$container = Page::create('alliance_broadcast.php');
		$container->addVar('alliance_id');
	} else {
		$container = Page::create('message_send.php');
		if (isset($var['receiver'])) {
			$container->addVar('receiver');
		}
	}
	$container['preview'] = $message;
	$container->go();
}

if (empty($message)) {
	create_error('You have to enter a message to send!');
}

if (isset($var['alliance_id'])) {
	$db = Database::getInstance();
	$dbResult = $db->read('SELECT account_id FROM player
				WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
				AND alliance_id = ' . $var['alliance_id'] . '
				AND account_id != ' . $db->escapeNumber($player->getAccountID())); //No limit in case they are over limit - ie NHA
	foreach ($dbResult->records() as $dbRecord) {
		$player->sendMessage($dbRecord->getInt('account_id'), MSG_ALLIANCE, $message, false);
	}
	$player->sendMessage($player->getAccountID(), MSG_ALLIANCE, $message, true, false);
} elseif (isset($var['receiver'])) {
	$player->sendMessage($var['receiver'], MSG_PLAYER, $message);
} else {
	$player->sendGlobalMessage($message);
}

if (isset($var['alliance_id'])) {
	$container = Page::create('alliance_roster.php');
	$container->addVar('alliance_id');
} else {
	$container = Page::create('current_sector.php');
}
$container['msg'] = '<span class="green">SUCCESS: </span>Your message has been sent.';
$container->go();
