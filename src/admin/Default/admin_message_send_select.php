<?php declare(strict_types=1);

$template = Smr\Template::getInstance();

$template->assign('PageTopic', 'Send Admin Message');

$template->assign('AdminMessageChooseGameFormHref', Page::create('skeleton.php', 'admin_message_send.php')->href());

// Get a list of all games that have not yet ended
$activeGames = array();
$db = Smr\Database::getInstance();
$db->query('SELECT game_id FROM game WHERE end_time > ' . $db->escapeNumber(Smr\Epoch::time()) . ' ORDER BY end_time DESC');
while ($db->nextRecord()) {
	$activeGames[] = SmrGame::getGame($db->getInt('game_id'));
}
$template->assign('ActiveGames', $activeGames);
