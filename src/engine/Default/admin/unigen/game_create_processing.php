<?php declare(strict_types=1);

use Smr\Database;
use Smr\Request;

$db = Database::getInstance();

//first create the game
$dbResult = $db->read('SELECT 1 FROM game WHERE game_name=' . $db->escapeString(Request::get('game_name')));
if ($dbResult->hasRecord()) {
	create_error('That game name is already taken.');
}

$dbResult = $db->read('SELECT IFNULL(MAX(game_id), 0) AS max_game_id FROM game');
$newID = $dbResult->record()->getInt('max_game_id') + 1;

// Get the dates ("|" sets hr/min/sec to 0)
$join = DateTime::createFromFormat('d/m/Y|', Request::get('game_join'));
if ($join === false) {
	create_error('Join Date is not valid!');
}
$start = empty(Request::get('game_start')) ? $join :
	DateTime::createFromFormat('d/m/Y|', Request::get('game_start'));
if ($start === false) {
	create_error('Start Date is not valid!');
}
$end = DateTime::createFromFormat('d/m/Y|', Request::get('game_end'));
if ($end === false) {
	create_error('End Date is not valid!');
}

$game = SmrGame::createGame($newID);
$game->setName(Request::get('game_name'));
$game->setDescription(Request::get('desc'));
$game->setGameTypeID(Request::getInt('game_type'));
$game->setMaxTurns(Request::getInt('max_turns'));
$game->setStartTurnHours(Request::getInt('start_turns'));
$game->setMaxPlayers(Request::getInt('max_players'));
$game->setAllianceMaxPlayers(Request::getInt('alliance_max_players'));
$game->setAllianceMaxVets(Request::getInt('alliance_max_vets'));
$game->setJoinTime($join->getTimestamp());
$game->setStartTime($start->getTimestamp());
$game->setEndTime($end->getTimestamp());
$game->setGameSpeed(Request::getFloat('game_speed'));
$game->setIgnoreStats(Request::get('ignore_stats') == 'Yes');
$game->setStartingCredits(Request::getInt('starting_credits'));
$game->setCreditsNeeded(Request::getInt('creds_needed'));
$game->setStartingRelations(Request::getInt('relations'));

// Start game disabled by default
$game->setEnabled(false);
$game->save();

$container = Page::create('admin/unigen/universe_create_galaxies.php');
$container['game_id'] = $game->getGameID();
$container->go();
