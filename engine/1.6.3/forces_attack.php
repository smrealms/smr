<?php

$results = unserialize($var['results']);
$template->assignByRef('FullForceCombatResults',$results);

if($var['owner_id']>0)
	$template->assignByRef('Target',SmrForce::getForce($player->getGameID(),$player->getSectorID(),$var['owner_id']));

if(isset($var['override_death']))
	$template->assign('OverrideDeath',true);
else
	$template->assign('OverrideDeath',false);
?>