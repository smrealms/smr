<?php declare(strict_types=1);

$session = SmrSession::getInstance();

if (!isset($var['GameID'])) {
	$session->updateVar('GameID', $player->getGameID());
}
$gameID = $var['GameID'];

$template->assign('PageTopic', 'Current News');
Menu::news($template);

require_once(get_file_loc('news.inc.php'));
doBreakingNewsAssign($gameID, $template);
doLottoNewsAssign($gameID, $template);


if (!isset($var['LastNewsUpdate'])) {
	$session->updateVar('LastNewsUpdate', $player->getLastNewsUpdate());
}

$db->query('SELECT * FROM news WHERE game_id = ' . $db->escapeNumber($gameID) . ' AND time > ' . $db->escapeNumber($var['LastNewsUpdate']) . ' AND type != \'lotto\' ORDER BY news_id DESC');
$template->assign('NewsItems', getNewsItems($db));

$player->updateLastNewsUpdate();
