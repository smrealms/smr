<?php
if(!isset($var['GameID'])) SmrSession::updateVar('GameID',$player->getGameID());
$gameID = $var['GameID'];

if (isset($_REQUEST['min_news'])) $min_news = $_REQUEST['min_news'];
if (isset($_REQUEST['max_news'])) $max_news = $_REQUEST['max_news'];
if (empty($min_news) || empty($max_news)) {
	$min_news = 1;
	$max_news = 50;
}
elseif ($min_news > $max_news)
		create_error('The first number must be lower than the second number!');

$template->assign('PageTopic','Reading The News');

require_once(get_file_loc('menu.inc'));
create_news_menu($template);

require_once(get_file_loc('news.functions.inc'));
doBreakingNewsAssign($gameID,$template);
doLottoNewsAssign($gameID,$template);

$template->assign('ViewNewsFormHref',SmrSession::getNewHREF(create_container('skeleton.php','news_read.php',array('GameID'=>$var['GameID']))));

$db->query('SELECT * FROM news WHERE game_id = '.$db->escapeNumber($gameID).' AND type != \'breaking\' ORDER BY news_id DESC LIMIT ' . ($min_news - 1) . ', ' . ($max_news - $min_news + 1));
if ($db->getNumRows()) {
	$NewsItems = array();
	while ($db->nextRecord()) {
		$NewsItems[] = array('Time' => $db->getField('time'), 'Message' => bbifyMessage($db->getField('news_message')), 'Type' => $db->getField('type'));
	}
	$template->assign('NewsItems',$NewsItems);
}
?>