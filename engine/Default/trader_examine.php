<?php

// Get the player we're attacking
$targetPlayer =& SmrPlayer::getPlayer($var['target'],$player->getGameID());

if($targetPlayer->isDead())
{
	$container=array();
	$container['url'] = 'skeleton.php';
	$container['body'] = 'current_sector.php';
	$container['msg'] = '<span class="red bold">ERROR:</span> Target already dead.';
	forward($container);
}


$template->assign('PageTopic','Examine Ship');
// should we display a attack button
$template->assignByRef('TargetPlayer',$targetPlayer);
?>