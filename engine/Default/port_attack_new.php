<?
require_once(get_file_loc('SmrPort.class.inc'));
$results = unserialize($var['results']);
$smarty->assign_by_ref('FullPortCombatResults',$results);
if(isset($var['override_death']))
	$smarty->assign('OverrideDeath',true);
else
	$smarty->assign('OverrideDeath',false);
$smarty->assign_by_ref('Port',SmrPort::getPort($player->getGameID(),$var['sector_id']));
?>