<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$db = Smr\Database::getInstance();
$session = Smr\Session::getInstance();
$account = $session->getAccount();

//get information
$container = Page::create('1.6/game_create_processing.php');
$template->assign('CreateGalaxiesHREF', $container->href());

$container = Page::create('skeleton.php', '1.6/universe_create_sectors.php');
$template->assign('EditGameHREF', $container->href());

$canEditStartedGames = $account->hasPermission(PERMISSION_EDIT_STARTED_GAMES);
$template->assign('CanEditStartedGames', $canEditStartedGames);

$defaultGame = [
	'name' => '',
	'description' => '',
	'speed' => 1.5,
	'maxTurns' => DEFAULT_MAX_TURNS,
	'startTurnHours' => DEFAULT_START_TURN_HOURS,
	'maxPlayers' => 5000,
	'joinDate' => date('d/m/Y', Smr\Epoch::time()),
	'startDate' => '',
	'endDate' => date('d/m/Y', Smr\Epoch::time() + (2 * 31 * 86400)), // 3 months
	'smrCredits' => 0,
	'gameType' => 'Default',
	'allianceMax' => 25,
	'allianceMaxVets' => 15,
	'startCredits' => 100000,
	'ignoreStats' => false,
	'relations' => MIN_GLOBAL_RELATIONS,
];
$template->assign('Game', $defaultGame);
$template->assign('SubmitValue', 'Create Game');

$games = array();
if ($canEditStartedGames) {
	$dbResult = $db->read('SELECT game_id FROM game ORDER BY end_time DESC');
} else {
	$dbResult = $db->read('SELECT game_id FROM game WHERE join_time > ' . $db->escapeNumber(Smr\Epoch::time()) . ' ORDER BY end_time DESC');
}
foreach ($dbResult->records() as $dbRecord) {
	$games[] = SmrGame::getGame($dbRecord->getInt('game_id'));
}
$template->assign('EditGames', $games);
