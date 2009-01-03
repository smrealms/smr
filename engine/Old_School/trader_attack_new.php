<?
$results = unserialize($var['results']);
$smarty->assign_by_ref('TraderCombatResults',$results);
if($var['target'])
	$smarty->assign_by_ref('Target',SmrPlayer::getPlayer($var['target'],SmrSession::$game_id));
if(isset($var['override_death']))
	$smarty->assign('OverrideDeath',true);
else
	$smarty->assign('OverrideDeath',false);

?>