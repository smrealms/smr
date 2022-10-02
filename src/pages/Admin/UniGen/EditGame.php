<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$var = Smr\Session::getInstance()->getCurrentVar();

$template->assign('PageTopic', 'Edit Game Details');

$gameID = $var['game_id'];

// Use Alskant-Creonti as a proxy for the starting political relations
$relations = Globals::getRaceRelations($gameID, RACE_ALSKANT)[RACE_CREONTI];

$game = SmrGame::getGame($gameID);
$gameArray = [
	'name' => $game->getName(),
	'description' => $game->getDescription(),
	'speed' => $game->getGameSpeed(),
	'maxTurns' => $game->getMaxTurns(),
	'startTurnHours' => $game->getStartTurnHours(),
	'maxPlayers' => $game->getMaxPlayers(),
	'joinDate' => date('d/m/Y', $game->getJoinTime()),
	'startDate' => date('d/m/Y', $game->getStartTime()),
	'endDate' => date('d/m/Y', $game->getEndTime()),
	'smrCredits' => $game->getCreditsNeeded(),
	'gameType' => $game->getGameType(),
	'allianceMax' => $game->getAllianceMaxPlayers(),
	'allianceMaxVets' => $game->getAllianceMaxVets(),
	'startCredits' => $game->getStartingCredits(),
	'ignoreStats' => $game->isIgnoreStats(),
	'relations' => $relations,
];
$template->assign('Game', $gameArray);

$container = Page::create('admin/unigen/game_edit_processing.php');
$container->addVar('game_id');
$container->addVar('gal_on');
$template->assign('ProcessingHREF', $container->href());
$template->assign('SubmitValue', 'Modify Game');

$container = Page::create('admin/unigen/universe_create_sectors.php');
$container->addVar('game_id');
$container->addVar('gal_on');
$template->assign('CancelHREF', $container->href());
