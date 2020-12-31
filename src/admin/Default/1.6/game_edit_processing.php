<?php declare(strict_types=1);

// Get the dates ("|" sets hr/min/sec to 0)
$join = DateTime::createFromFormat('d/m/Y|', Request::get('game_join'));
$start = empty(Request::get('game_start')) ? $join :
         DateTime::createFromFormat('d/m/Y|', Request::get('game_start'));
$end = DateTime::createFromFormat('d/m/Y|', Request::get('game_end'));

$game = SmrGame::getGame($var['game_id']);
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
if (!$game->hasStarted()) {
	$game->setStartingRelations(Request::getInt('relations'));
}
$game->save();

$container = create_container('skeleton.php', '1.6/universe_create_sectors.php');
$container['message'] = '<span class="green">SUCCESS: edited game details</span>';
transfer('game_id');
transfer('gal_on');
forward($container);
