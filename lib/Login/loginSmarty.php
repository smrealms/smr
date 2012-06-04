<?php
// new db object
$db = new SmrMySqlDatabase();

$loginNews = array();
$db->query('SELECT * FROM game_news ORDER BY time DESC LIMIT 3');
while ($db->nextRecord())
{
	$loginNews[] = array('Message' => $db->getField('message'),'AdminName' => $db->getField('admin_name'),'Time' => date(DATE_DATE_SHORT,$db->getField('time')), 'Recent' => (TIME - $db->getField('time') < 24 * 3600));
}
$smarty->assign('LoginNews',$loginNews);


$db->query('SELECT count(*) AS num_on_cpl FROM player WHERE last_cpl_action > '.(TIME - 3600));
$db->nextRecord();
$smarty->assign('NumberOnCPL',$db->getField('num_on_cpl'));

$gameNews = array();
$db->query('SELECT * FROM news ORDER BY time DESC LIMIT 4');
while ($db->nextRecord())
{
	$gameNews[] = array('Date' => date(DATE_DATE_SHORT,$db->getField('time')), 'Time' => date(DATE_TIME_SHORT,$db->getField('time')), 'Message' => $db->getField('news_message'));
}
$smarty->assign('GameNews',$gameNews);

include_once('story.php');

$smarty->display('login.tpl');
?>
