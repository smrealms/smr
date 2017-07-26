<?php

$template->assign('PageTopic', 'Enable New Games');

// If we have just forwarded from the processing file, pass its message.
$template->assign('ProcessingMsg', $var['processing_msg']);

// Get the list of disabled games
$db->query('SELECT * FROM game WHERE enabled=' . $db->escapeBoolean(false));
$disabledGames = array();
while ($db->nextRecord()) {
	$disabledGames[] = array('game_name' => $db->getField('game_name'),
	                         'game_id' => $db->getInt('game_id'));
}
$template->assign('DisabledGames', $disabledGames);

// Create the link to the processing file
$link_container = create_container('enable_game_processing.php', '');
$template->assign('EnableGameHREF', SmrSession::getNewHREF($link_container));

?>
