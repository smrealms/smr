<?php

$template->assign('GameTypes', SmrGame::GAME_TYPES);

//get information
$container = create_container('1.6/game_create_processing.php');
$template->assign('CreateGalaxiesHREF',SmrSession::getNewHREF($container));

$container = create_container('skeleton.php', '1.6/universe_create_sectors.php');
$template->assign('EditGameHREF',SmrSession::getNewHREF($container));

$canEditStartedGames = $account->hasPermission(PERMISSION_EDIT_STARTED_GAMES);
$template->assign('CanEditStartedGames', $canEditStartedGames);

$defaultGame = [
	'name' => '',
	'description' => '',
	'speed' => 1.5,
	'maxTurns' => DEFAULT_MAX_TURNS,
	'startTurnHours' => DEFAULT_START_TURN_HOURS,
	'maxPlayers' => 5000,
	'startDate' => date('d/m/Y', TIME),
	'startTurnsDate' => '',
	'endDate' => date('d/m/Y', TIME + (2 * 31 * 86400)), // 3 months
	'smrCredits' => 0,
	'allianceMax' => 25,
	'allianceMaxVets' => 15,
	'startCredits' => 100000,
	'ignoreStats' => false,
];
$template->assign('Game', $defaultGame);

$games = array();
if ($canEditStartedGames) {
	$db->query('SELECT game_id FROM game ORDER BY end_date DESC');
} else {
	$db->query('SELECT game_id FROM game WHERE start_date > ' . $db->escapeNumber(TIME) . ' ORDER BY end_date DESC');
}
while ($db->nextRecord()) {
	$games[] = SmrGame::getGame($db->getInt('game_id'));
}
$template->assign('EditGames',$games);
