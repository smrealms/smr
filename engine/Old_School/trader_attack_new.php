<?

$smarty->assign('TraderCombatResults',unserialize($var['results']));
if($var['target'])
	$smarty->assign('Target',SmrPlayer::getPlayer($var['target'],SmrSession::$game_id));
if(isset($var['override_death']))
	$smarty->assign('OverrideDeath',true);
else
	$smarty->assign('OverrideDeath',false);

?>