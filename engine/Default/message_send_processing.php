<?php

$message = htmlentities(trim($_REQUEST['message']));

if($_REQUEST['action'] == 'Preview message')
{
	$container = create_container('skeleton.php');
	if(isset($var['alliance_id']))
		$container['body'] = 'alliance_broadcast.php';
	else
		$container['body'] = 'message_send.php';
	transfer('receiver');
	transfer('alliance_id');
	$container['preview'] = $message;
	forward($container);
}

if (empty($message))
	create_error('You have to enter a message to send!');

if(isset($var['alliance_id']))
{
	$db->query('SELECT account_id FROM player WHERE game_id=' . $player->getGameID() . 
				' AND alliance_id=' . $var['alliance_id']); //No limit in case they are over limit - ie NHA
	while ($db->nextRecord())
	{
		$player->sendMessage($db->getField('account_id'), MSG_ALLIANCE, $message,false);
	}
}
else if (!empty($var['receiver']))
{
	$player->sendMessage($var['receiver'], MSG_PLAYER, $message);
}
else
{
	$player->sendGlobalMessage($message);
}

$container=create_container('skeleton.php');
if(isset($var['alliance_id']))
{
	$container['body'] = 'alliance_roster.php';
	transfer('alliance_id');
}
else if ($player->isLandedOnPlanet())
	$container['body'] = 'planet_main.php';
else
	$container['body'] = 'current_sector.php';
$container['msg'] = '<span class="green">SUCCESS: </span>Your message has been sent.';
forward($container);

?>