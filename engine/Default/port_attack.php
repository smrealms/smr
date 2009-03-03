<?
require_once(get_file_loc('SmrPort.class.inc'));
if(isset($var['results']))
{
	$results = unserialize($var['results']);
	$template->assignByRef('FullPortCombatResults',$results);
}
else
	$template->assign('AlreadyDestroyed',true);
if(isset($var['override_death']))
	$template->assign('OverrideDeath',true);
else
	$template->assign('OverrideDeath',false);
$template->assignByRef('Port',SmrPort::getPort($player->getGameID(),$var['sector_id']));
?>