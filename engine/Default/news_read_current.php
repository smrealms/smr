<?php
if (!isset($var['GameID'])) {
	SmrSession::updateVar('GameID', $player->getGameID());
}
$gameID = $var['GameID'];

$template->assign('PageTopic', 'Current News');
Menu::news($template);

require_once(get_file_loc('news.functions.inc'));
doBreakingNewsAssign($gameID, $template);
doLottoNewsAssign($gameID, $template);


if (!isset($var['LastNewsUpdate'])) {
	SmrSession::updateVar('LastNewsUpdate', $player->getLastNewsUpdate());
}

$db->query('SELECT * FROM news WHERE game_id = ' . $db->escapeNumber($gameID) . ' AND time > ' . $db->escapeNumber($var['LastNewsUpdate']) . ' AND type != \'lotto\' ORDER BY news_id DESC');
$template->assign('NewsItems', getNewsItems($db));

$player->updateLastNewsUpdate();
