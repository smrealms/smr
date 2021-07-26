<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();

$gameID = $var['GameID'] ?? $session->getPlayer()->getGameID();

$min_news = Smr\Request::getInt('min_news', 1);
$max_news = Smr\Request::getInt('max_news', 50);
if ($min_news > $max_news) {
	create_error('The first number must be lower than the second number!');
}
$template->assign('MinNews', $min_news);
$template->assign('MaxNews', $max_news);

$template->assign('PageTopic', 'Reading The News');

Menu::news($gameID);

require_once(get_file_loc('news.inc.php'));
doBreakingNewsAssign($gameID);
doLottoNewsAssign($gameID);

$template->assign('ViewNewsFormHref', Page::create('skeleton.php', 'news_read.php', ['GameID' => $gameID])->href());

$db = Smr\Database::getInstance();
$dbResult = $db->read('SELECT * FROM news WHERE game_id = ' . $db->escapeNumber($gameID) . ' AND type != \'lotto\' ORDER BY news_id DESC LIMIT ' . ($min_news - 1) . ', ' . ($max_news - $min_news + 1));
$template->assign('NewsItems', getNewsItems($dbResult));
