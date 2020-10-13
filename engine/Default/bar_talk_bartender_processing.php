<?php declare(strict_types=1);

$container = create_container('skeleton.php', 'bar_talk_bartender.php');
transfer('LocationID');

$action = Request::get('action');

if ($action == 'tell') {
	$gossip = Request::get('gossip_tell');
	if (!empty($gossip)) {
		$db->query('SELECT message_id FROM bar_tender WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' ORDER BY message_id DESC LIMIT 1');
		if ($db->nextRecord()) {
			$amount = $db->getInt('message_id') + 1;
		} else {
			$amount = 1;
		}

		$db->query('INSERT INTO bar_tender (game_id, message_id, message) VALUES (' . $db->escapeNumber($player->getGameID()) . ', ' . $db->escapeNumber($amount) . ',  ' . $db->escapeString($gossip) . ' )');
		SmrAccount::doMessageSendingToBox($player->getPlayerID(), BOX_BARTENDER, $gossip, $player->getGameID());

		$container['Message'] = 'Huh, that\'s news to me...<br /><br />Got anything else to tell me?';
	} else {
		$container['Message'] = 'So you\'re the tight-lipped sort, eh? No matter, no matter...<br /><br /><i>The bartender slowly scans the room with squinted eyes and then leans in close.</i><br /><br />Must be a sensational story you\'ve got there. Don\'t worry, I can keep a secret. What\'s on your mind?';
	}
} elseif ($action == 'tip') {
	$event = SmrEnhancedWeaponEvent::getLatestEvent($player->getGameID());
	$cost = $event->getWeapon()->getCost();

	// Tip needs to be more than a specific fraction of the weapon cost
	$tip = Request::getInt('tip');
	$player->decreaseCredits($tip);
	$container['Message'] = '<i>The bartender notices your ' . number_format($tip) . ' credit tip.</i><br /><br />';
	if ($tip > 0.25 * $cost) {
		$eventSectorID = $event->getSectorID();
		$eventGalaxy = SmrGalaxy::getGalaxyContaining($player->getGameID(), $eventSectorID);
		if ($player->getSector()->getGalaxy()->equals($eventGalaxy)) {
			$locationHint = 'Sector ' . Globals::getSectorBBLink($eventSectorID);
		} else {
			$locationHint = 'the ' . $eventGalaxy->getName() . ' galaxy';
		}
		if ($event->getWeapon()->hasBonusDamage() && $event->getWeapon()->hasBonusAccuracy()) {
			$qualifier = 'very special';
		} else {
			$qualifier = 'special';
		}
		$container['Message'] .= 'Thank you kindly!<br /><br /><i>The bartender begins to turn away, hesitates, and then turns back to you.</i><br /><br />By the way, I heard that a weapon shop in ' . $locationHint . ' has some ' . $qualifier . ' stock that a person like you just might be interested in. That\'s all I know about it...<br /><br />Got anything to tell me?';
	} elseif ($tip > 0.05 * $cost) {
		$container['Message'] .= 'Oh, so it\'s secrets you\'re after, eh? Well, it\'ll cost ya more than that...<br /><br />Got anything to tell me?';
	} else {
		$container['Message'] .= 'Thanks, I guess...<br /><br />Got anything to tell me?';
	}
}

forward($container);
