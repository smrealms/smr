<?php declare(strict_types=1);

$template->assign('PageTopic', 'Game News : ' . $var['game_name']);
Menu::history_games(3);

if (isset($_REQUEST['min'])) {
	$min = $_REQUEST['min'];
} else {
	$min = 1;
}
if (isset($_REQUEST['max'])) {
	$max = $_REQUEST['max'];
} else {
	$max = 50;
}
$template->assign('Max', $max);
$template->assign('Min', $min);

$template->assign('ShowHREF', SmrSession::getNewHREF($var));

$db = new $var['HistoryDatabase']();
$db->query('SELECT * FROM news WHERE game_id = ' . $db->escapeNumber($var['view_game_id']) . ' AND news_id >= ' . $db->escapeNumber($min) . ' AND news_id <= ' . $db->escapeNumber($max));
$rows = [];
while ($db->nextRecord()) {
	$rows[] = [
		'time' => date(DATE_FULL_SHORT, $db->getInt('time')),
		'news' => $db->getField('message'),
	];
}
$template->assign('Rows', $rows);

$db = new SmrMySqlDatabase();
