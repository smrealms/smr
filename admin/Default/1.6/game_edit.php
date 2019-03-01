<?php

$template->assign('PageTopic', 'Edit Game Details');

$gameID = SmrSession::getRequestVar('game_id');

$game = SmrGame::getGame($gameID);
$gameArray = [
	'name' => $game->getName(),
	'description' => $game->getDescription(),
	'speed' => $game->getGameSpeed(),
	'maxTurns' => $game->getMaxTurns(),
	'startTurnHours' => $game->getStartTurnHours(),
	'maxPlayers' => $game->getMaxPlayers(),
	'startDate' => date('d/m/Y', $game->getStartDate()),
	'startTurnsDate' => date('d/m/Y', $game->getStartTurnsDate()),
	'endDate' => date('d/m/Y', $game->getEndDate()),
	'smrCredits' => $game->getCreditsNeeded(),
	'gameType' => $game->getGameType(),
	'allianceMax' => $game->getAllianceMaxPlayers(),
	'allianceMaxVets' => $game->getAllianceMaxVets(),
	'startCredits' => $game->getStartingCredits(),
	'ignoreStats' => $game->isIgnoreStats(),
];
$template->assign('Game', $gameArray);

$container = create_container('1.6/game_edit_processing.php');
transfer('game_id');
$template->assign('ProcessingHREF', SmrSession::getNewHREF($container));
$template->assign('SubmitValue', 'Modify Game');

$container = create_container('skeleton.php', '1.6/universe_create_sectors.php');
transfer('game_id');
$template->assign('CancelHREF', SmrSession::getNewHREF($container));
