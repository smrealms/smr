<?php

//get information
$container = create_container('1.6/game_create_processing.php');
$template->assign('CreateGalaxiesHREF', SmrSession::getNewHREF($container));

$container = create_container('skeleton.php', '1.6/universe_create_sectors.php');
$template->assign('EditGameHREF', SmrSession::getNewHREF($container));

$canEditStartedGames = $account->hasPermission(PERMISSION_EDIT_STARTED_GAMES);
$template->assign('CanEditStartedGames', $canEditStartedGames);

$defaultGame = [
	'name' => '',
	'description' => '',
	'speed' => 1.5,
	'maxTurns' => DEFAULT_MAX_TURNS,
	'startTurnHours' => DEFAULT_START_TURN_HOURS,
	'maxPlayers' => 5000,
	'joinDate' => date('d/m/Y', TIME),
	'startDate' => '',
	'endDate' => date('d/m/Y', TIME + (2 * 31 * 86400)), // 3 months
	'smrCredits' => 0,
	'gameType' => 'Default',
	'allianceMax' => 25,
	'allianceMaxVets' => 15,
	'startCredits' => 100000,
	'ignoreStats' => false,
];
$template->assign('Game', $defaultGame);
$template->assign('SubmitValue', 'Create Game');

$games = array();
if ($canEditStartedGames) {
	$db->query('SELECT game_id FROM game ORDER BY end_time DESC');
} else {
	$db->query('SELECT game_id FROM game WHERE join_time > ' . $db->escapeNumber(TIME) . ' ORDER BY end_time DESC');
}
while ($db->nextRecord()) {
	$games[] = SmrGame::getGame($db->getInt('game_id'));
}
$template->assign('EditGames', $games);
