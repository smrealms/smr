<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();

$gameID = $var['GameID'] ?? $player->getGameID();

$template->assign('PageTopic', 'Current News');
Menu::news();

require_once(get_file_loc('news.inc.php'));
doBreakingNewsAssign($gameID);
doLottoNewsAssign($gameID);

if (!isset($var['LastNewsUpdate'])) {
	$var['LastNewsUpdate'] = $player->getLastNewsUpdate();
}

$db = Smr\Database::getInstance();
$dbResult = $db->read('SELECT * FROM news WHERE game_id = ' . $db->escapeNumber($gameID) . ' AND time > ' . $db->escapeNumber($var['LastNewsUpdate']) . ' AND type != \'lotto\' ORDER BY news_id DESC');
$template->assign('NewsItems', getNewsItems($dbResult));

$player->updateLastNewsUpdate();
