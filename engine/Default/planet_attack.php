<?php
require_once(get_file_loc('SmrPlanet.class.inc'));
if(isset($var['results'])) {
	$results = unserialize($var['results']);
	$template->assignByRef('FullPlanetCombatResults',$results);
	$template->assign('AlreadyDestroyed',false);
}
else
	$template->assign('AlreadyDestroyed',true);
$template->assign('MinimalDisplay',false);
if(isset($var['override_death']))
	$template->assign('OverrideDeath',true);
else
	$template->assign('OverrideDeath',false);
$template->assignByRef('Planet',SmrPlanet::getPlanet($player->getGameID(),$var['sector_id']));
?>