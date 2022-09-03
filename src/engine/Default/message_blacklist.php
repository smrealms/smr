<?php declare(strict_types=1);

use Smr\Database;

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();

$template->assign('PageTopic', 'Player Blacklist');

Menu::messages();

if (isset($var['msg'])) {
	$template->assign('Message', $var['msg']);
}

$db = Database::getInstance();
$dbResult = $db->read('SELECT p.player_name, p.game_id, b.entry_id FROM player p JOIN message_blacklist b ON p.account_id = b.blacklisted_id AND b.game_id = p.game_id WHERE b.account_id=' . $db->escapeNumber($player->getAccountID()) . ' ORDER BY p.game_id, p.player_name');

$blacklist = [];
foreach ($dbResult->records() as $dbRecord) {
	$blacklist[] = $dbRecord->getRow();
}
$template->assign('Blacklist', $blacklist);

if ($blacklist) {
	$container = Page::create('message_blacklist_del.php');
	$template->assign('BlacklistDeleteHREF', $container->href());
}

$container = Page::create('message_blacklist_add.php');
$template->assign('BlacklistAddHREF', $container->href());
