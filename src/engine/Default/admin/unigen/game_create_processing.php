<?php declare(strict_types=1);

$db = Smr\Database::getInstance();

//first create the game
$dbResult = $db->read('SELECT 1 FROM game WHERE game_name=' . $db->escapeString(Smr\Request::get('game_name')) . ' LIMIT 1');
if ($dbResult->hasRecord()) {
	create_error('That game name is already taken.');
}

$dbResult = $db->read('SELECT game_id FROM game ORDER BY game_id DESC LIMIT 1');
if ($dbResult->hasRecord()) {
	$newID = $dbResult->record()->getInt('game_id') + 1;
} else {
	$newID = 1;
}

// Get the dates ("|" sets hr/min/sec to 0)
$join = DateTime::createFromFormat('d/m/Y|', Smr\Request::get('game_join'));
if ($join === false) {
	create_error('Join Date is not valid!');
}
$start = empty(Smr\Request::get('game_start')) ? $join :
         DateTime::createFromFormat('d/m/Y|', Smr\Request::get('game_start'));
if ($start === false) {
	create_error('Start Date is not valid!');
}
$end = DateTime::createFromFormat('d/m/Y|', Smr\Request::get('game_end'));
if ($end === false) {
	create_error('End Date is not valid!');
}

$game = SmrGame::createGame($newID);
$game->setName(Smr\Request::get('game_name'));
$game->setDescription(Smr\Request::get('desc'));
$game->setGameTypeID(Smr\Request::getInt('game_type'));
$game->setMaxTurns(Smr\Request::getInt('max_turns'));
$game->setStartTurnHours(Smr\Request::getInt('start_turns'));
$game->setMaxPlayers(Smr\Request::getInt('max_players'));
$game->setAllianceMaxPlayers(Smr\Request::getInt('alliance_max_players'));
$game->setAllianceMaxVets(Smr\Request::getInt('alliance_max_vets'));
$game->setJoinTime($join->getTimestamp());
$game->setStartTime($start->getTimestamp());
$game->setEndTime($end->getTimestamp());
$game->setGameSpeed(Smr\Request::getFloat('game_speed'));
$game->setIgnoreStats(Smr\Request::get('ignore_stats') == 'Yes');
$game->setStartingCredits(Smr\Request::getInt('starting_credits'));
$game->setCreditsNeeded(Smr\Request::getInt('creds_needed'));
$game->setStartingRelations(Smr\Request::getInt('relations'));

// Start game disabled by default
$game->setEnabled(false);
$game->save();

$container = Page::create('skeleton.php', 'admin/unigen/universe_create_galaxies.php');
$container['game_id'] = $game->getGameID();
$container->go();
