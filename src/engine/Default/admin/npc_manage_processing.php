<?php declare(strict_types=1);

use Smr\Database;
use Smr\Exceptions\AllianceNotFound;
use Smr\Request;

$db = Database::getInstance();
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
	$npcPlayer = SmrPlayer::createPlayer($accountID, $gameID, $playerName, $raceID, false, true);

	$npcPlayer->getShip()->setHardwareToMax();
	$npcPlayer->giveStartingTurns();
	$npcPlayer->setCredits(SmrGame::getGame($gameID)->getStartingCredits());

	// Prevent them from triggering the newbie warning page
	$npcPlayer->setNewbieWarning(false);

	// Give a random alignment
	$npcPlayer->setAlignment(rand(-300, 300));

	$allianceName = Request::get('player_alliance');
	try {
		$alliance = SmrAlliance::getAllianceByName($allianceName, $gameID);
	} catch (AllianceNotFound) {
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
	$email = $login . '@smrealms.de';
	$npcAccount = SmrAccount::createAccount($login, '', $email, 0, 0);
	$npcAccount->setValidated(true);
	$npcAccount->update();
	$db->insert('npc_logins', [
		'login' => $db->escapeString($login),
		'player_name' => $db->escapeString(Request::get('default_player_name')),
		'alliance_name' => $db->escapeString(Request::get('default_alliance')),
	]);
}

$container = Page::create('admin/npc_manage.php');
$container->addVar('selected_game_id');
$container->go();
