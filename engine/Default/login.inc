<?php
// new db object
$db = new SmrMySqlDatabase();

$gameNews = array();
$db->query('SELECT * FROM news ORDER BY time DESC LIMIT 4');
while ($db->nextRecord()) {
	$overrideGameID = $db->getInt('game_id');
	$gameNews[] = array('Date' => date(DEFAULT_DATE_DATE_SHORT,$db->getField('time')), 'Time' => date(DEFAULT_DATE_TIME_SHORT,$db->getField('time')), 'Message' => bbifyMessage($db->getField('news_message')));
}
unset($overrideGameID);
if(count($gameNews)>0)
	$template->assign('GameNews',$gameNews);

require_once('login_story.inc');

$template->display('login.inc');
