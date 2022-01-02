<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();

$gameID = $var['GameID'] ?? $player->getGameID();

$template->assign('PageTopic', 'Current News');
Menu::news($gameID);

Smr\News::doBreakingNewsAssign($gameID);
Smr\News::doLottoNewsAssign($gameID);

if (!isset($var['LastNewsUpdate'])) {
	$var['LastNewsUpdate'] = $player->getLastNewsUpdate();
}

$db = Smr\Database::getInstance();
$dbResult = $db->read('SELECT * FROM news WHERE game_id = ' . $db->escapeNumber($gameID) . ' AND time > ' . $db->escapeNumber($var['LastNewsUpdate']) . ' AND type != \'lotto\' ORDER BY news_id DESC');
$template->assign('NewsItems', Smr\News::getNewsItems($dbResult));

$player->updateLastNewsUpdate();
