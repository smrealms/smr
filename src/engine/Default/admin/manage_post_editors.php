<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();

$template->assign('PageTopic', 'Manage Galactic Post Editors');

$container = Page::create('admin/manage_post_editors.php');
$template->assign('SelectGameHREF', $container->href());

// Get the list of active games ordered by reverse start date
$activeGames = [];
$db = Smr\Database::getInstance();
$dbResult = $db->read('SELECT game_id, game_name FROM game WHERE join_time < ' . $db->escapeNumber(Smr\Epoch::time()) . ' AND end_time > ' . $db->escapeNumber(Smr\Epoch::time()) . ' ORDER BY start_time DESC');
foreach ($dbResult->records() as $dbRecord) {
	$activeGames[] = [
		'game_name' => $dbRecord->getString('game_name'),
		'game_id' => $dbRecord->getInt('game_id'),
	];
}
$template->assign('ActiveGames', $activeGames);

if ($activeGames) {
	// Set the selected game (or the first in the list if not selected yet)
	$selectedGameID = $session->getRequestVarInt('selected_game_id', $activeGames[0]['game_id']);
	$template->assign('SelectedGame', $selectedGameID);

	// Get the list of current editors for the selected game
	$currentEditors = [];
	foreach (Globals::getGalacticPostEditorIDs($selectedGameID) as $editorID) {
		$editor = SmrPlayer::getPlayer($editorID, $selectedGameID);
		$currentEditors[] = $editor->getDisplayName();
	}
	$template->assign('CurrentEditors', $currentEditors);
}

if (isset($var['processing_msg'])) {
	if (Smr\Request::has('game_id')) {
		// If we are selecting a different game, clear the processing message.
		unset($var['processing_msg']);
	} else {
		// If we have just forwarded from the processing file, pass its message.
		$template->assign('ProcessingMsg', $var['processing_msg']);
	}
}

// Create the link to the processing file
// Pass entire $var so the processing file knows the selected game
$linkContainer = Page::create('admin/manage_post_editors_processing.php', $var);
$template->assign('PostEditorHREF', $linkContainer->href());
