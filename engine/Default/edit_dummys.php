<?php
$smarty->assign('PageTopic','Edit Dummys');

require_once(get_file_loc('DummyPlayer.class.inc'));
require_once(get_file_loc('DummyShip.class.inc'));
require_once(get_file_loc('SmrWeapon.class.inc'));
//TODO add game type id
$smarty->assign_by_ref('CombatSimLink',SmrSession::get_new_href(create_container('skeleton.php','combat_simulator.php')));
$smarty->assign_by_ref('BaseShips',AbstractSmrShip::getAllBaseShips(0));
$smarty->assign_by_ref('Weapons',SmrWeapon::getAllWeapons(0));

$smarty->assign('EditDummysLink',SmrSession::get_new_href(create_container('skeleton.php','edit_dummys.php')));

$dummyPlayer =& DummyPlayer::getCachedDummyPlayer($_REQUEST['dummy_name']);
$dummyShip =& $dummyPlayer->getShip();

if(isset($_REQUEST['save_dummy']))
{
	$dummyPlayer->setPlayerName($_REQUEST['dummy_name']);
	$dummyPlayer->setExperience($_REQUEST['level']);
	$dummyPlayer->setShipTypeID($_REQUEST['ship_id']);
	$dummyShip->regenerate($dummyPlayer);
	$dummyShip->removeAllWeapons();
	foreach($_REQUEST['weapons'] as $weaponTypeID)
	{
		$dummyShip->addWeapon($weaponTypeID);
	}
	$dummyPlayer->cacheDummyPlayer();
}


$smarty->assign_by_ref('DummyPlayer',$dummyPlayer);
$smarty->assign_by_ref('DummyShip',$dummyShip);
$smarty->assign_by_ref('ShipWeapons',$dummyShip->getWeapons());
$smarty->assign_by_ref('Levels',Globals::getLevelRequirements());



$smarty->assign('DummyNames', DummyPlayer::getDummyPlayerNames());
?>