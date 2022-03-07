<?php declare(strict_types=1);

$template = Smr\Template::getInstance();

$template->assign('PageTopic', 'Send Admin Message');

$template->assign('AdminMessageChooseGameFormHref', Page::create('skeleton.php', 'admin/admin_message_send.php')->href());

// Get a list of all games that have not yet ended
$activeGames = [];
$db = Smr\Database::getInstance();
$dbResult = $db->read('SELECT game_id FROM game WHERE end_time > ' . $db->escapeNumber(Smr\Epoch::time()) . ' ORDER BY end_time DESC');
foreach ($dbResult->records() as $dbRecord) {
	$activeGames[] = SmrGame::getGame($dbRecord->getInt('game_id'));
}
$template->assign('ActiveGames', $activeGames);
