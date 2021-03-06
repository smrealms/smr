<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$account = $session->getAccount();

$template->assign('PageTopic', 'Game News : ' . $var['game_name']);
Menu::history_games(3);

$min = Request::getInt('min', 1);
$max = Request::getInt('max', 50);
$template->assign('Max', $max);
$template->assign('Min', $min);

$template->assign('ShowHREF', Page::copy($var)->href());

$db = Smr\Database::getInstance();
$db->switchDatabases($var['HistoryDatabase']);
$dbResult = $db->read('SELECT * FROM news WHERE game_id = ' . $db->escapeNumber($var['view_game_id']) . ' AND news_id >= ' . $db->escapeNumber($min) . ' AND news_id <= ' . $db->escapeNumber($max));
$rows = [];
foreach ($dbResult->records() as $dbRecord) {
	$rows[] = [
		'time' => date($account->getDateTimeFormat(), $dbRecord->getInt('time')),
		'news' => $dbRecord->getField('message'),
	];
}
$template->assign('Rows', $rows);

$db->switchDatabaseToLive(); // restore database
