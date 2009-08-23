<?php

$message = trim($_REQUEST['message']);
if($_REQUEST['action'] == 'Preview message')
{
	$container = create_container('skeleton.php','admin_message_send.php');
	transfer('GameID');
	$container['preview'] = $message;
	forward($container);
}

$account_id = $_REQUEST['account_id'];
$game_id = $var['GameID'];
if (!empty($account_id) || $game_id == 20000)
{
	$expire = $_REQUEST['expire'];
	if ($expire > 0) $expire = ($expire * 3600) + TIME;
	if ($game_id != 20000)
	{
		SmrPlayer::sendMessageFromAdmin($game_id, $account_id, $message,$expire);
	}
	else
	{
		//send to all players
		$db->query('SELECT game_id,account_id FROM player');
		while ($db->nextRecord())
		{
			SmrPlayer::sendMessageFromAdmin($db->getField('game_id'), $db->getField('account_id'), $message,$expire);
		}
	}
}
$container = create_container('skeleton.php', 'game_play.php');
$container['msg'] = '<span class="green">SUCCESS: </span>Your message has been sent.';
forward($container)

?>