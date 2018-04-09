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
if(!is_numeric($var['game_id']))
	create_error('Game ID is not numeric');

$gameID = $var['game_id'];

$db->query('SELECT 1 FROM player WHERE game_id = ' . $db->escapeNumber($gameID) . ' AND player_name = ' . $db->escape_string($player_name, true) . ' LIMIT 1');
if ($db->nextRecord() > 0)
	create_error('The player name already exists.');

if (!Globals::isValidGame($gameID))
	create_error('Game not found!');

// does it cost something to join that game?
$credits = Globals::getGameCreditsRequired($gameID);
if ($credits > 0) {
	if($account->getTotalSmrCredits() < $credits) {
		create_error('You do not have enough credits to join this game!');
	}
	$account->decreaseTotalSmrCredits($credits);
}

// check if hof entry is there
$db->query('SELECT 1 FROM account_has_stats WHERE account_id = '.$db->escapeNumber(SmrSession::$account_id) . ' LIMIT 1');
if (!$db->nextRecord()) {
	$db->query('INSERT INTO account_has_stats (account_id, HoF_name) VALUES ('.$db->escapeNumber($account->getAccountID()).', ' . $db->escape_string($account->getLogin(), true) . ')');
}

// put him in a sector with a hq
$hq_id = $race_id + 101;
$db->query('SELECT * FROM location JOIN sector USING(game_id, sector_id) ' .
		'WHERE game_id = ' . $db->escapeNumber($gameID) . ' AND ' .
		'location_type_id = '.$db->escapeNumber($hq_id));
if ($db->nextRecord()) {
	$home_sector_id = $db->getInt('sector_id');
}
else {
	$home_sector_id = 1;
}

// get rank_id
$rank_id = $account->getRank();

// for newbie and beginner another ship, more shields and armour
if ($account->isNewbie()) {
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

$last_turn_update = SmrGame::getGame($gameID)->getStartTurnsDate();

//// newbie leaders need to put into there alliances
if (SmrSession::$account_id == ACCOUNT_ID_NHL) {
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
$db->query('INSERT INTO player (account_id, game_id, player_id, player_name, race_id, ship_type_id, credits, alliance_id, sector_id, last_turn_update, last_cpl_action, last_active, newbie_turns, npc)
			VALUES(' . $db->escapeNumber(SmrSession::$account_id) . ', ' . $db->escapeNumber($gameID) . ', '.$db->escapeNumber($player_id).', ' . $db->escape_string($player_name, true) . ', '.$db->escapeNumber($race_id).', '.$db->escapeNumber($ship_id).', '.$db->escapeNumber(Globals::getStartingCredits($gameID)).', '.$db->escapeNumber($alliance_id).', '.$db->escapeNumber($home_sector_id).', '.$db->escapeNumber($last_turn_update).', ' . $db->escapeNumber(TIME) . ', ' . $db->escapeNumber(TIME) . ',' . $db->escapeNumber($startingNewbieTurns) . ',' . $db->escapeBoolean(defined('NPC_SCRIPT')) . ')');

$db->unlock();

// give the player shields
$db->query('INSERT INTO ship_has_hardware (account_id, game_id, hardware_type_id, amount, old_amount)
			VALUES(' . $db->escapeNumber(SmrSession::$account_id) . ', ' . $db->escapeNumber($gameID) . ', 1, '.$db->escapeNumber($amount_shields).', '.$db->escapeNumber($amount_shields).')');
// give the player armour
$db->query('INSERT INTO ship_has_hardware (account_id, game_id, hardware_type_id, amount, old_amount)
			VALUES(' . $db->escapeNumber(SmrSession::$account_id) . ', ' . $db->escapeNumber($gameID) . ', 2, '.$db->escapeNumber($amount_armour).', '.$db->escapeNumber($amount_armour).')');
// give the player cargo hold
$db->query('INSERT INTO ship_has_hardware (account_id, game_id, hardware_type_id, amount, old_amount)
			VALUES(' . $db->escapeNumber(SmrSession::$account_id) . ', ' . $db->escapeNumber($gameID) . ', 3, 40, 40)');
// give the player weapons
$db->query('INSERT INTO ship_has_weapon (account_id, game_id, order_id, weapon_type_id)
			VALUES(' . $db->escapeNumber(SmrSession::$account_id) . ', ' . $db->escapeNumber($gameID) . ', 0, 46)');

// insert the huge amount of sectors into the database :)
$db->query('SELECT MIN(sector_id), MAX(sector_id)
			FROM sector
			WHERE game_id = ' . $db->escapeNumber($gameID));
if (!$db->nextRecord()) {
	create_error('This game doesn\'t have any sectors');
}

$min_sector = $db->getInt('MIN(sector_id)');
$max_sector = $db->getInt('MAX(sector_id)');

for ($i = $min_sector; $i <= $max_sector; $i++) {
	//if this is our home sector we dont add it.
	if ($i == $home_sector_id) {
		continue;
	}

	$db->query('INSERT INTO player_visited_sector (account_id, game_id, sector_id) VALUES (' . $db->escapeNumber(SmrSession::$account_id) . ', ' . $db->escapeNumber($gameID) . ', '.$db->escapeNumber($i).')');
}
$db->query('INSERT INTO player_has_stats (account_id, game_id) VALUES (' . $db->escapeNumber(SmrSession::$account_id) . ', ' . $db->escapeNumber($gameID) . ')');



// update stats
$db->query('UPDATE account_has_stats SET games_joined = games_joined + 1 WHERE account_id = '.$db->escapeNumber($account->getAccountID()));

// is this our first game?
$db->query('SELECT * FROM account_has_stats WHERE account_id = '.$db->escapeNumber($account->getAccountID()));
if ($db->nextRecord() && $db->getInt('games_joined') == 1) {
	//we are a newb set our alliance to be Newbie Help Allaince
	$db->query('UPDATE player SET alliance_id = '.$db->escapeNumber(NHA_ID).' WHERE account_id = '.$db->escapeNumber($account->getAccountID()).' AND game_id = '.$db->escapeNumber($gameID));
	$db->query('INSERT INTO player_has_alliance_role (game_id, account_id, role_id,alliance_id) VALUES ('.$db->escapeNumber($gameID).', '.$db->escapeNumber($account->getAccountID()).', 2,'.$db->escapeNumber(NHA_ID).')');
	//we need to send them some messages
	$message = 'Welcome to Space Merchant Realms, this message is to get you underway with information to start you off in the game. All newbie and beginner rated player are placed into a teaching alliance run by a Veteran player who is experienced enough to answer all your questions and give you a helping hand at learning the basics of the game.<br /><br />
	Apart from your leader (denoted with a star on your alliance roster) there are various other ways to get information and help. Newbie helpers are players in Blue marked on the Current Players List which you can view by clicking the link on the left-hand side of the screen that says "Current Players". Also you can visit the SMR Wiki via a link on the left which gives detailed information on all aspects fo the game.<br /><br />
	SMR is a very community orientated game and as such there is an IRC Chat server setup for people to talk with each other and coordinate your alliances. There is a link on the left which will take you directly to the main SMR room where people come to hang out and chat. You can also get help in the game in the #smr room. You can access this by typing /join #smr-help in the server window. If you prefer to use a dedicated program to access IRC Chat rather than a browser you can goto http://www.mirc.com which is a good shareware program (asks to register the program after 30 days but you can still use it after 30 days so you won\'t get cut off from using it) or http://www.xchat.org which is a free alternative. In the options of either program you will need to enter the server information to access the server. Add a new server and enter the server address irc.theairlock.net using port 6667. Once connected you can use the /join command to join #smr (/join #smr) or any other room on the server as normal.<br /><br />
	Apart from this you can view the webboard via a link on the left to join in community chat and conversations, ask questions for help and make suggestions for the game in various forums.<br /><br />
	To get underway, click the alliance link on the left where you can get more information on 	how to get started on the alliance message board which will get you into your alliance chat 	on IRC so you can get started and have your questions answered.<br /><br />Depending on the size and resolution of your monitor the default font size may be too large or small. This can be changed using the preferences link on the left panel.';

	SmrPlayer::sendMessageFromAdmin($gameID, $account->getAccountID(), $message);
}

if($race_id == RACE_ALSKANT) { // Give Alskants 250 personal relations to start.
	$player =& SmrPlayer::getPlayer($account->getAccountID(), $gameID);
	$RACES =& Globals::getRaces();
	foreach($RACES as $raceID => $raceInfo) {
		$player->setRelations(250, $raceID);
	}
}
forward(create_container('skeleton.php', 'game_play.php'));
