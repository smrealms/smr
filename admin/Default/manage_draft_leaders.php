<?php declare(strict_types=1);

$template->assign('PageTopic', 'Manage Draft Leaders');

$container = create_container('skeleton.php', 'manage_draft_leaders.php');
$template->assign('SelectGameHREF', SmrSession::getNewHREF($container));

// Get the list of active Draft games ordered by reverse start date
$activeGames = array();
$db->query('SELECT game_id, game_name FROM game WHERE game_type=' . $db->escapeNumber(SmrGame::GAME_TYPE_DRAFT) . ' AND join_time < ' . $db->escapeNumber(TIME) . ' AND end_time > ' . $db->escapeNumber(TIME) . ' ORDER BY start_time DESC');
while ($db->nextRecord()) {
	$activeGames[] = array('game_name' => $db->getField('game_name'),
	                       'game_id' => $db->getInt('game_id'));
}
$template->assign('ActiveGames', $activeGames);

if ($activeGames) {
	// Set the selected game (or the first in the list if not selected yet)
	$selectedGameID = SmrSession::getRequestVarInt('selected_game_id', $activeGames[0]['game_id']);
	$template->assign('SelectedGame', $selectedGameID);

	// Get the list of current draft leaders for the selected game
	$currentLeaders = array();
	$db->query('SELECT account_id, home_sector_id FROM draft_leaders WHERE game_id=' . $db->escapeNumber($selectedGameID));
	while ($db->nextRecord()) {
		$homeSectorID = $db->getInt('home_sector_id');
		$leader = SmrPlayer::getPlayer($db->getInt('account_id'), $selectedGameID);
		$currentLeaders[] = [
			'Name' => $leader->getDisplayName(),
			'HomeSectorID' => $homeSectorID === 0 ? 'None' : $homeSectorID,
		];
	}
	$template->assign('CurrentLeaders', $currentLeaders);
}

// If we are selecting a different game, clear the processing message.
if (Request::has('selected_game_id')) {
	SmrSession::updateVar('processing_msg', null);
}
// If we have just forwarded from the processing file, pass its message.
if (isset($var['processing_msg'])) {
	$template->assign('ProcessingMsg', $var['processing_msg']);
}

// Create the link to the processing file
// Pass entire $var so the processing file knows the selected game
$linkContainer = create_container('manage_draft_leaders_processing.php', '', $var);
$template->assign('ProcessingHREF', SmrSession::getNewHREF($linkContainer));
