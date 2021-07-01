<?php declare(strict_types=1);

$db = Smr\Database::getInstance();
$var = Smr\Session::getInstance()->getCurrentVar();

// Change active status of an NPC
if (Request::has('active-submit')) {
	// Toggle the activity of this NPC
	$active = Request::has('active');
	$db->write('UPDATE npc_logins SET active=' . $db->escapeBoolean($active) . ' WHERE login=' . $db->escapeString($var['login']));
}

// Create a new NPC player in a selected game
if (Request::has('create_npc_player')) {
	$accountID = $var['accountID'];
	$gameID = $var['selected_game_id'];
	$playerName = Request::get('player_name');
	$raceID = Request::getInt('race_id');
	try {
		$npcPlayer = SmrPlayer::createPlayer($accountID, $gameID, $playerName, $raceID, false, true);
	} catch (\Smr\UserException $err) {
		create_error($err->getMessage());
	}

	$npcPlayer->getShip()->setHardwareToMax();
	$npcPlayer->giveStartingTurns();
	$npcPlayer->setCredits(SmrGame::getGame($gameID)->getStartingCredits());

	// Give a random alignment
	$npcPlayer->setAlignment(rand(-300, 300));

	$allianceName = Request::get('player_alliance');
	$alliance = SmrAlliance::getAllianceByName($allianceName, $gameID);
	if (is_null($alliance)) {
		$alliance = SmrAlliance::createAlliance($gameID, $allianceName);
		$alliance->setLeaderID($npcPlayer->getAccountID());
		$alliance->update();
		$alliance->createDefaultRoles();
	}
	$npcPlayer->joinAlliance($alliance->getAllianceID());

	// Update because we may not have a lock
	$npcPlayer->update();
	$npcPlayer->getShip()->update();
}

// Add a new NPC account
if (Request::has('add_npc_account')) {
	$login = Request::get('npc_login');
	$npcAccount = SmrAccount::createAccount($login, '', 'NPC@smrealms.de', 0, 0);
	$npcAccount->setValidated(true);
	$npcAccount->update();
	$db->write('INSERT INTO npc_logins (login, player_name, alliance_name) VALUES(' . $db->escapeString($login) . ',' . $db->escapeString(Request::get('default_player_name')) . ',' . $db->escapeString(Request::get('default_alliance')) . ')');
}

$container = Page::create('skeleton.php', 'npc_manage.php');
$container->addVar('selected_game_id');
$container->go();
