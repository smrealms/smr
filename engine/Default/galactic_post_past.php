<?php

require_once(get_file_loc('menu.inc'));
create_galactic_post_menu();
$template->assign('PageTopic', 'Past <i>Galactic Post</i> Editions');

// View past editions of current game by default
if (isset($_POST['game_id'])) {
	SmrSession::updateVar('game_id', $_POST['game_id']);
} else if (!isset($var['game_id'])) {
	SmrSession::updateVar('game_id', $player->getGameID());
}
$template->assign('SelectedGame', $var['game_id']);

// Get the list of games with published papers
// Add the current game to this list no matter what
$db->query('SELECT game_name, game_id FROM game WHERE game_id IN (SELECT DISTINCT game_id FROM galactic_post_paper WHERE online_since IS NOT NULL) OR game_id=' . $db->escapeNumber($player->getGameID()) . ' ORDER BY game_id DESC');
$publishedGames = array();
while ($db->nextRecord()) {
	$publishedGames[] = array('game_name' => $db->getField('game_name'),
	                          'game_id' => $db->getInt('game_id'));
}
$template->assign('PublishedGames', $publishedGames);

// Get the list of published papers for the selected game
$db->query('SELECT * FROM galactic_post_paper WHERE online_since IS NOT NULL AND game_id=' . $db->escapeNumber($var['game_id']));
$pastEditions = array();
while ($db->nextRecord()) {
	$container = create_container('skeleton.php', 'galactic_post_read.php');
	$container['paper_id'] = $db->getInt('paper_id');
	$container['game_id'] = $var['game_id'];
	$container['back'] = true;

	$pastEditions[] = array('title' => $db->getField('title'),
	                        'online_since' => $db->getField('online_since'),
	                        'href' => SmrSession::getNewHREF($container));
}
$template->assign('PastEditions', $pastEditions);

?>
