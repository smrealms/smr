<?
require_once(get_file_loc('SmrPort.class.inc'));
if(isset($var['results']))
{
	$results = unserialize($var['results']);
	$smarty->assign_by_ref('FullPortCombatResults',$results);
}
else
	$smarty->assign('AlreadyDestroyed',true);
if(isset($var['override_death']))
	$smarty->assign('OverrideDeath',true);
else
	$smarty->assign('OverrideDeath',false);
$smarty->assign_by_ref('Port',SmrPort::getPort($player->getGameID(),$var['sector_id']));
?>