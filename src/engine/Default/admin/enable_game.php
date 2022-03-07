<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();

$template->assign('PageTopic', 'Enable New Games');

// If we have just forwarded from the processing file, pass its message.
if (isset($var['processing_msg'])) {
	$template->assign('ProcessingMsg', $var['processing_msg']);
}

// Get the list of disabled games
$db = Smr\Database::getInstance();
$dbResult = $db->read('SELECT game_name, game_id FROM game WHERE enabled=' . $db->escapeBoolean(false));
$disabledGames = [];
foreach ($dbResult->records() as $dbRecord) {
	$disabledGames[$dbRecord->getInt('game_id')] = $dbRecord->getField('game_name');
}
krsort($disabledGames);
$template->assign('DisabledGames', $disabledGames);

// Create the link to the processing file
$linkContainer = Page::create('admin/enable_game_processing.php', '');
$template->assign('EnableGameHREF', $linkContainer->href());
