<?php

$template->assign('PageTopic','Send Message');
$gameID = isset($_REQUEST['game_id'])?$_REQUEST['game_id'] : (isset($var['game_id']) ? $var['game_id'] : null);
// check if we know the game yet
if (empty($gameID))
{
	$template->assign('AdminMessageChooseGameFormHref',SmrSession::get_new_href(create_container('skeleton.php', 'admin_message_send.php')));
	$template->assignByRef('Games',Globals::getGameInfo());
}
else
{
	$container =create_container('admin_message_send_processing.php');
	$container['game_id']	= $gameID;
	$template->assign('AdminMessageSendFormHref',SmrSession::get_new_href($container));
	$template->assign('MessageGameID',$gameID);

	if ($gameID != 20000)
	{
		$PHP_OUTPUT.=('<select name="account_id" size="1" id="InputFields">');
		$PHP_OUTPUT.=('<option value="0">[Please Select]</option>');
	
		$gamePlayers = array();
		$db->query('SELECT * FROM player WHERE game_id = '.$gameID.' ORDER BY player_id');
		while ($db->nextRecord())
			$gamePlayers[]= array('AccountID' => $db->getField('account_id'), 'PlayerID' => $db->getField('player_id'), 'Name' => $db->getField('player_name'));
		$template->assignByRef('GamePlayers',$gamePlayers);
	}
	if(isset($var['preview']))
		$template->assign('Preview', $var['preview']);
}
?>