<?
$results = unserialize($var['results']);
$template->assignByRef('TraderCombatResults',$results);
if($var['target'])
	$template->assignByRef('Target',SmrPlayer::getPlayer($var['target'],SmrSession::$game_id));
if(isset($var['override_death']))
	$template->assign('OverrideDeath',true);
else
	$template->assign('OverrideDeath',false);

?>