<?php

$template->assign('PageTopic','Send Message');
if(isset($_REQUEST['game_id']))
	SmrSession::updateVar('GameID',$_REQUEST['game_id']);
$gameID = $var['GameID'];
// check if we know the game yet
if (empty($gameID))
{
	$template->assign('AdminMessageChooseGameFormHref',SmrSession::get_new_href(create_container('skeleton.php', 'admin_message_send.php')));
	$template->assignByRef('Games',Globals::getGameInfo());
}
else
{
	$container =create_container('admin_message_send_processing.php');
	$container['GameID']	= $gameID;
	$template->assign('AdminMessageSendFormHref',SmrSession::get_new_href($container));
	$template->assign('MessageGameID',$gameID);
	$template->assign('ExpireTime', 1);

	if ($gameID != 20000)
	{
		$gamePlayers = array();
		$db->query('SELECT account_id,player_id,player_name FROM player WHERE game_id = '.$gameID.' ORDER BY player_name');
		while ($db->nextRecord())
			$gamePlayers[]= array('AccountID' => $db->getField('account_id'), 'PlayerID' => $db->getField('player_id'), 'Name' => $db->getField('player_name'));
		$template->assignByRef('GamePlayers',$gamePlayers);
	}
	if(isset($var['preview'])) {
		$template->assign('Preview', $var['preview']);
		$template->assign('ExpireTime', $var['expire']);
	}
}
?>