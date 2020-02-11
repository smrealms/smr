<?php declare(strict_types=1);

$container = create_container('skeleton.php', 'bar_talk_bartender.php');
transfer('LocationID');

$gossip = Request::get('gossip_tell');
if (!empty($gossip)) {
	$db->query('SELECT message_id FROM bar_tender WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' ORDER BY message_id DESC LIMIT 1');
	if ($db->nextRecord()) {
		$amount = $db->getInt('message_id') + 1;
	} else {
		$amount = 1;
	}

	$db->query('INSERT INTO bar_tender (game_id, message_id, message) VALUES (' . $db->escapeNumber($player->getGameID()) . ', ' . $db->escapeNumber($amount) . ',  ' . $db->escapeString($gossip) . ' )');
	SmrAccount::doMessageSendingToBox($player->getAccountID(), BOX_BARTENDER, $gossip, $player->getGameID());

	$container['Message'] = 'Huh, that\'s news to me...<br /><br />Got anything else to tell me?';
}

forward($container);
