<?php
if (isset($_REQUEST['min_news'])) $min_news = $_REQUEST['min_news'];
if (isset($_REQUEST['max_news'])) $max_news = $_REQUEST['max_news'];
if (empty($min_news) || empty($max_news))
{
	$min_news = 1;
	$max_news = 50;
}
elseif ($min_news > $max_news)
		create_error('The first number must be lower than the second number!');

$template->assign('PageTopic','Reading The News');

require_once(get_file_loc('menue.inc'));
create_news_menue($template);

require_once(get_file_loc('news.functions.inc'));
doBreakingNewsAssign($player->getGameID(),$template);
doLottoNewsAssign($player->getGameID(),$template);

$template->assign('ViewNewsFormHref',SmrSession::get_new_href(create_container('skeleton.php','news_read.php')));

$db->query('SELECT * FROM news WHERE game_id = '.$player->getGameID().' AND type != \'breaking\' ORDER BY news_id DESC LIMIT ' . ($min_news - 1) . ', ' . ($max_news - $min_news + 1));
if ($db->getNumRows())
{
	$NewsItems = array();
	while ($db->nextRecord())
	{
		$NewsItems[] = array('Time' => $db->getField('time'), 'Message' => $db->getField('news_message'));
	}
	$template->assign('NewsItems',$NewsItems);
}
?>