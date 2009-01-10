<?

$results = unserialize($var['results']);
$smarty->assign_by_ref('FullForceCombatResults',$results);

if($var['owner_id']>0)
	$smarty->assign_by_ref('Target',SmrForce::getForce($player->getGameID(),$player->getSectorID(),$var['owner_id']));

if(isset($var['override_death']))
	$smarty->assign('OverrideDeath',true);
else
	$smarty->assign('OverrideDeath',false);
?>