<?php

// Change active status of an NPC
if (isset($_POST['active-submit'])) {
	// Toggle the activity of this NPC
	$active = isset($_POST['active']);
	$db->query('UPDATE npc_logins SET active=' . $db->escapeBoolean($active) . ' WHERE login=' . $db->escapeString($var['login']));
}

// Create a new NPC player in a selected game
if (isset($_POST['create_npc_player'])) {
	$accountID = $var['accountID'];
	$gameID = $var['selected_game_id'];
	$playerName = $_POST['player_name'];
	$raceID = $_POST['race_id'];
	$npcPlayer = SmrPlayer::createPlayer($accountID, $gameID, $playerName, $raceID, false, true);

	$npcPlayer->getShip()->setHardwareToMax();
	$npcPlayer->giveStartingTurns();

	// Give a random alignment
	$npcPlayer->setAlignment(rand(-300, 300));

	$allianceName = $_POST['player_alliance'];
	$alliance = SmrAlliance::getAllianceByName($allianceName, $gameID);
	if (is_null($alliance)) {
		$alliance = SmrAlliance::createAlliance($gameID, $allianceName, '*', false);
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
if (isset($_POST['add_npc_account'])) {
	$login = $_POST['npc_login'];
	$npcAccount = SmrAccount::createAccount($login, '', 'NPC@smrealms.de', 0, 0);
	$npcAccount->setValidated(true);
	$db->query('INSERT INTO npc_logins (login, player_name, alliance_name) VALUES(' . $db->escapeString($login) . ',' . $db->escapeString($_POST['default_player_name']) . ',' . $db->escapeString($_POST['default_alliance']) . ')');
}

$container = create_container('skeleton.php', 'npc_manage.php');
transfer('selected_game_id');
forward($container);
