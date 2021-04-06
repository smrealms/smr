<?php declare(strict_types=1);

$session = SmrSession::getInstance();

$template->assign('PageTopic', 'Manage Galactic Post Editors');

$container = Page::create('skeleton.php', 'manage_post_editors.php');
$template->assign('SelectGameHREF', $container->href());

// Get the list of active games ordered by reverse start date
$activeGames = array();
$db->query('SELECT game_id, game_name FROM game WHERE join_time < ' . $db->escapeNumber(Smr\Epoch::time()) . ' AND end_time > ' . $db->escapeNumber(Smr\Epoch::time()) . ' ORDER BY start_time DESC');
while ($db->nextRecord()) {
	$activeGames[] = [
		'game_name' => $db->getField('game_name'),
		'game_id' => $db->getInt('game_id'),
	];
}
$template->assign('ActiveGames', $activeGames);

if ($activeGames) {
	// Set the selected game (or the first in the list if not selected yet)
	$selectedGameID = $session->getRequestVarInt('selected_game_id', $activeGames[0]['game_id']);
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
	$session->updateVar('processing_msg', null);
}
// If we have just forwarded from the processing file, pass its message.
if (isset($var['processing_msg'])) {
	$template->assign('ProcessingMsg', $var['processing_msg']);
}

// Create the link to the processing file
// Pass entire $var so the processing file knows the selected game
$linkContainer = Page::create('manage_post_editors_processing.php', '', $var);
$template->assign('PostEditorHREF', $linkContainer->href());
