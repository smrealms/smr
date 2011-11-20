<?php
$results = unserialize($var['results']);
$template->assignByRef('TraderCombatResults',$results);
if($var['target'])
	$template->assignByRef('Target',SmrPlayer::getPlayer($var['target'],$player->getGameID()));
if(isset($var['override_death']))
	$template->assign('OverrideDeath',true);
else
	$template->assign('OverrideDeath',false);
?>