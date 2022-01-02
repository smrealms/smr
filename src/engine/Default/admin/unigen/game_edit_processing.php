<?php declare(strict_types=1);

$var = Smr\Session::getInstance()->getCurrentVar();

// Get the dates ("|" sets hr/min/sec to 0)
$join = DateTime::createFromFormat('d/m/Y|', Smr\Request::get('game_join'));
$start = empty(Smr\Request::get('game_start')) ? $join :
         DateTime::createFromFormat('d/m/Y|', Smr\Request::get('game_start'));
$end = DateTime::createFromFormat('d/m/Y|', Smr\Request::get('game_end'));

$game = SmrGame::getGame($var['game_id']);
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
if (!$game->hasStarted()) {
	$game->setStartingRelations(Smr\Request::getInt('relations'));
}
$game->save();

$container = Page::create('skeleton.php', 'admin/unigen/universe_create_sectors.php');
$container['message'] = '<span class="green">SUCCESS: edited game details</span>';
$container->addVar('game_id');
$container->addVar('gal_on');
$container->go();
