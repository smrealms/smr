<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();

$template->assign('PageTopic', 'Manage Draft Leaders');

$container = Page::create('skeleton.php', 'manage_draft_leaders.php');
$template->assign('SelectGameHREF', $container->href());

// Get the list of active Draft games ordered by reverse start date
$activeGames = array();
$db = Smr\Database::getInstance();
$dbResult = $db->read('SELECT game_id, game_name FROM game WHERE game_type=' . $db->escapeNumber(SmrGame::GAME_TYPE_DRAFT) . ' AND join_time < ' . $db->escapeNumber(Smr\Epoch::time()) . ' AND end_time > ' . $db->escapeNumber(Smr\Epoch::time()) . ' ORDER BY start_time DESC');
foreach ($dbResult->records() as $dbRecord) {
	$activeGames[] = [
		'game_name' => $dbRecord->getField('game_name'),
		'game_id' => $dbRecord->getInt('game_id'),
	];
}
$template->assign('ActiveGames', $activeGames);

if ($activeGames) {
	// Set the selected game (or the first in the list if not selected yet)
	$selectedGameID = $session->getRequestVarInt('selected_game_id', $activeGames[0]['game_id']);
	$template->assign('SelectedGame', $selectedGameID);

	// Get the list of current draft leaders for the selected game
	$currentLeaders = array();
	$dbResult = $db->read('SELECT account_id, home_sector_id FROM draft_leaders WHERE game_id=' . $db->escapeNumber($selectedGameID));
	foreach ($dbResult->records() as $dbRecord) {
		$homeSectorID = $dbRecord->getInt('home_sector_id');
		$leader = SmrPlayer::getPlayer($dbRecord->getInt('account_id'), $selectedGameID);
		$currentLeaders[] = [
			'Name' => $leader->getDisplayName(),
			'HomeSectorID' => $homeSectorID === 0 ? 'None' : $homeSectorID,
		];
	}
	$template->assign('CurrentLeaders', $currentLeaders);
}

// If we are selecting a different game, clear the processing message.
if (Request::has('selected_game_id')) {
	$session->updateVar('processing_msg', null);
}
// If we have just forwarded from the processing file, pass its message.
if (isset($var['processing_msg'])) {
	$template->assign('ProcessingMsg', $var['processing_msg']);
}

// Create the link to the processing file
// Pass entire $var so the processing file knows the selected game
$linkContainer = Page::create('manage_draft_leaders_processing.php', '', $var);
$template->assign('ProcessingHREF', $linkContainer->href());
