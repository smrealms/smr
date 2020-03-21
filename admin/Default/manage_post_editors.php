<?php declare(strict_types=1);

$template->assign('PageTopic', 'Manage Galactic Post Editors');

$container = create_container('skeleton.php', 'manage_post_editors.php');
$template->assign('SelectGameHREF', SmrSession::getNewHREF($container));

// Get the list of active games ordered by reverse start date
$activeGames = array();
$db->query('SELECT game_id, game_name FROM game WHERE join_time < ' . $db->escapeNumber(TIME) . ' AND end_time > ' . $db->escapeNumber(TIME) . ' ORDER BY start_time DESC');
while ($db->nextRecord()) {
	$activeGames[] = array('game_name' => $db->getField('game_name'),
	                       'game_id' => $db->getInt('game_id'));
}
$template->assign('ActiveGames', $activeGames);

if ($activeGames) {
	// Set the selected game (or the first in the list if not selected yet)
	$selectedGameID = SmrSession::getRequestVarInt('selected_game_id', $activeGames[0]['game_id']);
	$template->assign('SelectedGame', $selectedGameID);

	// Get the list of current editors for the selected game
	$currentEditors = array();
	foreach (Globals::getGalacticPostEditorIDs($selectedGameID) as $editorID) {
		$editor = SmrPlayer::getPlayer($editorID, $selectedGameID);
		$currentEditors[] = $editor->getDisplayName();
	}
	$template->assign('CurrentEditors', $currentEditors);
}

// If we are selecting a different game, clear the processing message.
if (Request::has('game_id')) {
	SmrSession::updateVar('processing_msg', null);
}
// If we have just forwarded from the processing file, pass its message.
if (isset($var['processing_msg'])) {
	$template->assign('ProcessingMsg', $var['processing_msg']);
}

// Create the link to the processing file
// Pass entire $var so the processing file knows the selected game
$linkContainer = create_container('manage_post_editors_processing.php', '', $var);
$template->assign('PostEditorHREF', SmrSession::getNewHREF($linkContainer));
