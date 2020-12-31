<?php declare(strict_types=1);

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

$container = create_container('1.6/game_edit_processing.php');
transfer('game_id');
transfer('gal_on');
$template->assign('ProcessingHREF', SmrSession::getNewHREF($container));
$template->assign('SubmitValue', 'Modify Game');

$container = create_container('skeleton.php', '1.6/universe_create_sectors.php');
transfer('game_id');
transfer('gal_on');
$template->assign('CancelHREF', SmrSession::getNewHREF($container));
