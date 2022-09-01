<?php declare(strict_types=1);

use Smr\Database;
use Smr\Epoch;

$template = Smr\Template::getInstance();

$template->assign('PageTopic', 'Send Admin Message');

$template->assign('AdminMessageChooseGameFormHref', Page::create('admin/admin_message_send.php')->href());

// Get a list of all games that have not yet ended
$activeGames = [];
$db = Database::getInstance();
$dbResult = $db->read('SELECT game_id FROM game WHERE end_time > ' . $db->escapeNumber(Epoch::time()) . ' ORDER BY end_time DESC');
foreach ($dbResult->records() as $dbRecord) {
	$activeGames[] = SmrGame::getGame($dbRecord->getInt('game_id'));
}
$template->assign('ActiveGames', $activeGames);
