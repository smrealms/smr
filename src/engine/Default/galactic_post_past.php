<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$player = $session->getPlayer();

$template->assign('PageTopic', 'Past <i>Galactic Post</i> Editions');
Menu::galactic_post();

$container = Page::create('skeleton.php', 'galactic_post_past.php');
$template->assign('SelectGameHREF', $container->href());

// View past editions of current game by default
$selectedGameID = $session->getRequestVarInt('selected_game_id', $player->getGameID());
$template->assign('SelectedGame', $selectedGameID);

// Get the list of games with published papers
// Add the current game to this list no matter what
$db = Smr\Database::getInstance();
$db->query('SELECT game_name, game_id FROM game WHERE game_id IN (SELECT DISTINCT game_id FROM galactic_post_paper WHERE online_since IS NOT NULL) OR game_id=' . $db->escapeNumber($player->getGameID()) . ' ORDER BY game_id DESC');
$publishedGames = array();
while ($db->nextRecord()) {
	$publishedGames[] = [
		'game_name' => $db->getField('game_name'),
		'game_id' => $db->getInt('game_id'),
	];
}
$template->assign('PublishedGames', $publishedGames);

// Get the list of published papers for the selected game
$db->query('SELECT * FROM galactic_post_paper WHERE online_since IS NOT NULL AND game_id=' . $db->escapeNumber($selectedGameID));
$pastEditions = array();
while ($db->nextRecord()) {
	$container = Page::create('skeleton.php', 'galactic_post_read.php');
	$container['paper_id'] = $db->getInt('paper_id');
	$container['game_id'] = $selectedGameID;
	$container['back'] = true;

	$pastEditions[] = [
		'title' => $db->getField('title'),
		'online_since' => $db->getInt('online_since'),
		'href' => $container->href(),
	];
}
$template->assign('PastEditions', $pastEditions);
