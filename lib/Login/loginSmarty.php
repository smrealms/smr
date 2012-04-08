<?php
// new db object
$db = new SmrMySqlDatabase();

$loginNews = array();
$db->query('SELECT * FROM game_news ORDER BY time DESC LIMIT 2');
while ($db->nextRecord()) {
	$loginNews[] = array('Message' => $db->getField('message'),'AdminName' => $db->getField('admin_name'),'Time' => date(DEFAULT_DATE_DATE_SHORT,$db->getField('time')), 'Recent' => (TIME - $db->getField('time') < 24 * 3600));
}
if(count($loginNews)>0)
	$template->assign('LoginNews',$loginNews);


$db->query('SELECT count(*) AS active_sessions FROM active_session WHERE account_id!=0 AND last_accessed > '.$db->escapeNumber(TIME - SmrSession::TIME_BEFORE_EXPIRY));
$db->nextRecord();
$template->assign('ActiveSessions',$db->getField('active_sessions'));

$gameNews = array();
$db->query('SELECT * FROM news ORDER BY time DESC LIMIT 4');
while ($db->nextRecord()) {
	$overrideGameID = $db->getInt('game_id');
	$gameNews[] = array('Date' => date(DEFAULT_DATE_DATE_SHORT,$db->getField('time')), 'Time' => date(DEFAULT_DATE_TIME_SHORT,$db->getField('time')), 'Message' => bbifyMessage($db->getField('news_message')));
}
unset($overrideGameID);
if(count($gameNews)>0)
	$template->assign('GameNews',$gameNews);

include_once('story.php');

$template->display('login.inc');
?>
