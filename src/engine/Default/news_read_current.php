<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();

if (!isset($var['GameID'])) {
	$session->updateVar('GameID', $player->getGameID());
}
$gameID = $var['GameID'];

$template->assign('PageTopic', 'Current News');
Menu::news();

require_once(get_file_loc('news.inc.php'));
doBreakingNewsAssign($gameID);
doLottoNewsAssign($gameID);

if (!isset($var['LastNewsUpdate'])) {
	$session->updateVar('LastNewsUpdate', $player->getLastNewsUpdate());
}

$db = Smr\Database::getInstance();
$dbResult = $db->read('SELECT * FROM news WHERE game_id = ' . $db->escapeNumber($gameID) . ' AND time > ' . $db->escapeNumber($var['LastNewsUpdate']) . ' AND type != \'lotto\' ORDER BY news_id DESC');
$template->assign('NewsItems', getNewsItems($dbResult));

$player->updateLastNewsUpdate();
