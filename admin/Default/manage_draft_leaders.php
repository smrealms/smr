<?php declare(strict_types=1);

$template->assign('PageTopic', 'Manage Draft Leaders');

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
	if (isset($_POST['game_id'])) {
		SmrSession::updateVar('selected_game_id', $_POST['game_id']);
		SmrSession::updateVar('processing_msg', null);
	} elseif (!isset($var['selected_game_id'])) {
		SmrSession::updateVar('selected_game_id', $activeGames[0]['game_id']);
	}
	$template->assign('SelectedGame', $var['selected_game_id']);

	// Get the list of current draft leaders for the selected game
	$currentLeaders = array();
	$db->query('SELECT account_id FROM draft_leaders WHERE game_id=' . $db->escapeNumber($var['selected_game_id']));
	while ($db->nextRecord()) {
		$editor = SmrPlayer::getPlayer($db->getInt('account_id'),
		                               $var['selected_game_id']);
		$currentLeaders[] = $editor->getDisplayName();
	}
	$template->assign('CurrentLeaders', $currentLeaders);
}

// If we have just forwarded from the processing file, pass its message.
if (isset($var['processing_msg'])) {
	$template->assign('ProcessingMsg', $var['processing_msg']);
}

// Create the link to the processing file
// Pass entire $var so the processing file knows the selected game
$linkContainer = create_container('manage_draft_leaders_processing.php', '', $var);
$template->assign('ProcessingHREF', SmrSession::getNewHREF($linkContainer));
