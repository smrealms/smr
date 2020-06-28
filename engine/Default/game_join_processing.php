<?php declare(strict_types=1);

// trim input now
$player_name = trim(Request::get('player_name'));

if (strpos($player_name, 'NPC') === 0) {
	create_error('Player names cannot begin with "NPC".');
}

$limited_char = 0;
for ($i = 0; $i < strlen($player_name); $i++) {
	// disallow certain ascii chars
	if (ord($player_name[$i]) < 32 || ord($player_name[$i]) > 127) {
		create_error('The player name contains invalid characters!');
	}

	// numbers 48..57
	// Letters 65..90
	// letters 97..122
	if (!((ord($player_name[$i]) >= 48 && ord($player_name[$i]) <= 57) ||
		(ord($player_name[$i]) >= 65 && ord($player_name[$i]) <= 90) ||
		(ord($player_name[$i]) >= 97 && ord($player_name[$i]) <= 122))) {
		$limited_char += 1;
	}
}

if ($limited_char > 4) {
	create_error('You cannot use a name with more than 4 special characters.');
}

if (empty($player_name)) {
	create_error('You must enter a player name!');
}

$race_id = Request::getInt('race_id');
if (empty($race_id) || $race_id == RACE_NEUTRAL) {
	create_error('Please choose a race!');
}

$gameID = $var['game_id'];
$game = SmrGame::getGame($gameID);

// does it cost SMR Credits to join this game?
$creditsNeeded = $game->getCreditsNeeded();
if ($account->getTotalSmrCredits() < $creditsNeeded) {
	create_error('You do not have enough SMR credits to join this game!');
}
$account->decreaseTotalSmrCredits($creditsNeeded);

// for newbie and beginner another ship, more shields and armour
$isNewbie = !$account->isVeteran();
if ($isNewbie) {
	$startingNewbieTurns = STARTING_NEWBIE_TURNS_NEWBIE;
} else {
	$startingNewbieTurns = STARTING_NEWBIE_TURNS_VET;
}

// insert into player table.
$player = SmrPlayer::createPlayer($account->getAccountID(), $gameID, $player_name, $race_id, $isNewbie);

// Equip the ship
$player->getShip()->giveStarterShip();

$player->giveStartingTurns(); // must be done after setting ship
$player->setNewbieTurns($startingNewbieTurns);
$player->setCredits($game->getStartingCredits());

// The `player_visited_sector` table holds *unvisited* sectors, so that once
// all sectors are visited (the majority of the game), the table is empty.
$db->query('INSERT INTO player_visited_sector (account_id, game_id, sector_id)
            SELECT ' . $db->escapeNumber($account->getAccountID()) . ', game_id, sector_id
              FROM sector WHERE game_id = ' . $db->escapeNumber($gameID));

// Mark the player's start sector as visited
$player->getSector()->markVisited($player);

if ($isNewbie || $account->getAccountID() == ACCOUNT_ID_NHL) {
	// If player is a newb (or NHL), set alliance to be Newbie Help Allaince
	$player->joinAlliance(NHA_ID);

	//we need to send them some messages
	$message = 'Welcome to Space Merchant Realms! You have been automatically placed into the <u>' . $player->getAllianceBBLink() . '</u>, which is led by a veteran player who can assist you while you learn the basics of the game. Your alliance leader is denoted with a star on your alliance roster.<br />
	For more tips to help you get started with the game, check out your alliance message boards. These can be reached by clicking the "Alliance" link on the left side of the page, and then clicking the "Message Board" menu link. The <u><a href="' . WIKI_URL . '" target="_blank">SMR Wiki</a></u> also gives detailed information on all aspects of the game.<br />
	SMR is integrated with both IRC and Discord. These are free chat services where you can talk to other players and coordinate with your alliance. Simply click the "Join Chat" link at the bottom left panel of the page.';

	SmrPlayer::sendMessageFromAdmin($gameID, $account->getAccountID(), $message);
}

if ($race_id == RACE_ALSKANT) { // Give Alskants 250 personal relations to start.
	foreach (Globals::getRaces() as $raceID => $raceInfo) {
		$player->setRelations(250, $raceID);
	}
}

// We aren't in a game yet, so updates are not done automatically here
$player->update();
$player->getShip()->update();

// Announce the player joining in the news
$news = '[player=' . $player->getPlayerID() . '] has joined the game!';
$db->query('INSERT INTO news (time, news_message, game_id, type) VALUES (' . $db->escapeNumber(TIME) . ',' . $db->escapeString($news) . ',' . $db->escapeNumber($gameID) . ', \'admin\')');

// Send the player directly into the game
$container = create_container('game_play_processing.php');
transfer('game_id');
forward($container);
