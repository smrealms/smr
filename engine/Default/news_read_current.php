<?
$template->assign('PageTopic','CURRENT NEWS');
require_once(get_file_loc('menue.inc'));
$template->unassign('MenuBar');
create_news_menue($template);

require_once(get_file_loc('news.functions.inc'));
doBreakingNewsAssign($player->getGameID(),$template);
doLottoNewsAssign($player->getGameID(),$template);


if(!isset($var['LastNewsUpdate']))
	SmrSession::updateVar('LastNewsUpdate',$player->getLastNewsUpdate());

$db->query('SELECT * FROM news WHERE game_id = '.$player->getGameID().' AND time > '.$var['LastNewsUpdate'].' AND type = \'regular\' ORDER BY news_id DESC');
$player->updateLastNewsUpdate();
$player->update();

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