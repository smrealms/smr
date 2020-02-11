<?php declare(strict_types=1);

$template->assign('PageTopic', 'Send Admin Message');

$template->assign('AdminMessageChooseGameFormHref', SmrSession::getNewHREF(create_container('skeleton.php', 'admin_message_send.php')));

// Get a list of all games that have not yet ended
$activeGames = array();
$db->query('SELECT game_id FROM game WHERE end_time > ' . $db->escapeNumber(TIME) . ' ORDER BY end_time DESC');
while ($db->nextRecord()) {
	$activeGames[] = SmrGame::getGame($db->getInt('game_id'));
}
$template->assign('ActiveGames', $activeGames);
