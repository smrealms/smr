<?php
// new db object
$db = new SmrMySqlDatabase();

$gameNews = array();
$db->query('SELECT * FROM news WHERE type != \'admin\' ORDER BY time DESC LIMIT 4');
while ($db->nextRecord()) {
	$overrideGameID = $db->getInt('game_id');
	$gameNews[] = [
		'Time' => date(DEFAULT_DATE_FULL_SHORT_SPLIT, $db->getInt('time')),
		'Message' => bbifyMessage($db->getField('news_message')),
	];
}
$template->assign('GameNews', $gameNews);

unset($overrideGameID);

require_once('login_story.inc');

$template->display('login.inc');
