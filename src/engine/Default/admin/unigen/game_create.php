<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$db = Smr\Database::getInstance();
$session = Smr\Session::getInstance();
$account = $session->getAccount();

//get information
$container = Page::create('admin/unigen/game_create_processing.php');
$template->assign('CreateGalaxiesHREF', $container->href());

$container = Page::create('skeleton.php', 'admin/unigen/universe_create_sectors.php');
$template->assign('EditGameHREF', $container->href());

$canEditEnabledGames = $account->hasPermission(PERMISSION_EDIT_ENABLED_GAMES);
$template->assign('CanEditEnabledGames', $canEditEnabledGames);

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

$games = [];
if ($canEditEnabledGames) {
	$dbResult = $db->read('SELECT game_id FROM game ORDER BY game_id DESC');
} else {
	$dbResult = $db->read('SELECT game_id FROM game WHERE enabled=' . $db->escapeBoolean(false) . ' ORDER BY game_id DESC');
}
foreach ($dbResult->records() as $dbRecord) {
	$games[] = SmrGame::getGame($dbRecord->getInt('game_id'));
}
$template->assign('EditGames', $games);
