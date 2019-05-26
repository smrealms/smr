<?php

// trim input now
$player_name = trim($_REQUEST['player_name']);

if(!defined('NPC_SCRIPT') && strpos($player_name,'NPC')===0)
	create_error('Player names cannot begin with "NPC".');

$limited_char = 0;
for ($i = 0; $i < strlen($player_name); $i++) {
	// disallow certain ascii chars
	if (ord($player_name[$i]) < 32 || ord($player_name[$i]) > 127)
		create_error('The player name contains invalid characters!');

// numbers 48..57
// Letters 65..90
// letters 97..122
	if (!((ord($player_name[$i]) >= 48 && ord($player_name[$i]) <= 57) ||
		(ord($player_name[$i]) >= 65 && ord($player_name[$i]) <= 90) ||
		(ord($player_name[$i]) >= 97 && ord($player_name[$i]) <= 122))) {
		$limited_char += 1;
	}
}

if ($limited_char > 4)
	create_error('You cannot use a name with more than 4 special characters.');

if (empty($player_name))
	create_error('You must enter a player name!');

$race_id = $_REQUEST['race_id'];
if (empty($race_id) || $race_id == 1)
	create_error('Please choose a race!');

$gameID = $var['game_id'];
$game = SmrGame::getGame($gameID);

// Escape html elements so the name displays correctly
$player_name = htmlentities($player_name);

$db->query('SELECT 1 FROM player WHERE game_id = ' . $db->escapeNumber($gameID) . ' AND player_name = ' . $db->escapeString($player_name) . ' LIMIT 1');
if ($db->nextRecord() > 0)
	create_error('The player name already exists.');

// does it cost SMR Credits to join this game?
$creditsNeeded = $game->getCreditsNeeded();
if ($account->getTotalSmrCredits() < $creditsNeeded) {
	create_error('You do not have enough SMR credits to join this game!');
}
$account->decreaseTotalSmrCredits($creditsNeeded);

// put him in a sector with a hq
$home_sector_id = SmrPlayer::getHome($gameID, $race_id);

// for newbie and beginner another ship, more shields and armour
$isNewbie = !$account->isVeteran();
if ($isNewbie) {
	$startingNewbieTurns = STARTING_NEWBIE_TURNS_NEWBIE;
	$ship_id = SHIP_TYPE_NEWBIE_MERCHANT_VESSEL;
	$amount_shields = 75;
	$amount_armour = 150;
}
else {
	$startingNewbieTurns = STARTING_NEWBIE_TURNS_VET;
	switch($race_id) {
		case RACE_ALSKANT:
			$ship_id = SHIP_TYPE_SMALL_TIMER;
		break;
		case RACE_CREONTI:
			$ship_id = SHIP_TYPE_MEDIUM_CARGO_HULK;
		break;
		case RACE_HUMAN:
			$ship_id = SHIP_TYPE_LIGHT_FREIGHTER;
		break;
		case RACE_IKTHORNE:
			$ship_id = SHIP_TYPE_TINY_DELIGHT;
		break;
		case RACE_SALVENE:
			$ship_id = SHIP_TYPE_HATCHLINGS_DUE;
		break;
		case RACE_THEVIAN:
			$ship_id = SHIP_TYPE_SWIFT_VENTURE;
		break;
		case RACE_WQHUMAN:
			$ship_id = SHIP_TYPE_SLIP_FREIGHTER;
		break;
		case RACE_NIJARIN:
			$ship_id = SHIP_TYPE_REDEEMER;
		break;
		default:
			$ship_id = SHIP_TYPE_GALACTIC_SEMI;
	}
	$amount_shields = 50;
	$amount_armour = 50;
}

//// newbie leaders need to put into there alliances
if ($account->getAccountID() == ACCOUNT_ID_NHL) {
	$alliance_id = NHA_ID;
}
else {
	$alliance_id = 0;
}

$db->lockTable('player');

// get last registered player id in that game and increase by one.
$db->query('SELECT MAX(player_id) FROM player WHERE game_id = ' . $db->escapeNumber($gameID));
if ($db->nextRecord()) {
	$player_id = $db->getInt('MAX(player_id)') + 1;
}
else {
	$player_id = 1;
}

// insert into player table.
$db->query('INSERT INTO player (account_id, game_id, player_id, player_name, race_id, ship_type_id, alliance_id, sector_id, last_cpl_action, last_active, newbie_turns, npc, newbie_status)
			VALUES(' . $db->escapeNumber($account->getAccountID()) . ', ' . $db->escapeNumber($gameID) . ', '.$db->escapeNumber($player_id).', ' . $db->escapeString($player_name) . ', '.$db->escapeNumber($race_id).', '.$db->escapeNumber($ship_id).', '.$db->escapeNumber($alliance_id).', '.$db->escapeNumber($home_sector_id).', ' . $db->escapeNumber(TIME) . ', ' . $db->escapeNumber(TIME) . ',' . $db->escapeNumber($startingNewbieTurns) . ',' . $db->escapeBoolean(defined('NPC_SCRIPT')) . ',' . $db->escapeBoolean($isNewbie) . ')');

$db->unlock();

$player = SmrPlayer::getPlayer($account->getAccountID(), $gameID);
$player->giveStartingTurns();
$player->setCredits($game->getStartingCredits());

// Equip the ship
$ship = $player->getShip();
$ship->setShields($amount_shields, true);
$ship->setArmour($amount_armour, true);
$ship->setCargoHolds(40);
$ship->addWeapon(46);  // Laser

// The `player_visited_sector` table holds *unvisited* sectors, so that once
// all sectors are visited (the majority of the game), the table is empty.
$db->query('INSERT INTO player_visited_sector (account_id, game_id, sector_id)
            SELECT ' . $db->escapeNumber($account->getAccountID()) . ', game_id, sector_id
              FROM sector WHERE game_id = ' . $db->escapeNumber($gameID));

// Mark the player's start sector as visited
$player->getSector()->markVisited($player);

if ($isNewbie) {
	//we are a newb set our alliance to be Newbie Help Allaince
	$player->joinAlliance(NHA_ID);

	//we need to send them some messages
	$message = 'Welcome to Space Merchant Realms! You have been automatically placed into the <u>[alliance='.NHA_ID.']</u>, which is led by a veteran player who can assist you while you learn the basics of the game. Your alliance leader is denoted with a star on your alliance roster.<br />
	For more tips to help you get started with the game, check out your alliance message boards. These can be reached by clicking the "Alliance" link on the left side of the page, and then clicking the "Message Board" menu link. The <u><a href="'.WIKI_URL.'" target="_blank">SMR Wiki</a></u> also gives detailed information on all aspects of the game.<br />
	SMR is integrated with both IRC and Discord. These are free chat services where you can talk to other players and coordinate with your alliance. Simply click the "Join Chat" link at the bottom left panel of the page.';

	SmrPlayer::sendMessageFromAdmin($gameID, $account->getAccountID(), $message);
}

if($race_id == RACE_ALSKANT) { // Give Alskants 250 personal relations to start.
	foreach (Globals::getRaces() as $raceID => $raceInfo) {
		$player->setRelations(250, $raceID);
	}
}

// We aren't in a game yet, so updates are not done automatically here
$player->update();
$ship->update();

// Announce the player joining in the news
$news = '[player=' . $player->getPlayerID() . '] has joined the game!';
$db->query('INSERT INTO news (time, news_message, game_id, type) VALUES (' . $db->escapeNumber(TIME) . ',' . $db->escapeString($news) . ',' . $db->escapeNumber($gameID) . ', \'admin\')');

// Send the player directly into the game
$container = create_container('game_play_processing.php');
transfer('game_id');
forward($container);
