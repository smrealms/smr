<?php
$template->assign('PageTopic','Edit Dummys');

require_once(get_file_loc('DummyPlayer.class.inc'));
require_once(get_file_loc('DummyShip.class.inc'));
require_once(get_file_loc('SmrWeapon.class.inc'));
//TODO add game type id
$template->assign('CombatSimLink',SmrSession::get_new_href(create_container('skeleton.php','combat_simulator.php')));
$template->assignByRef('BaseShips',AbstractSmrShip::getAllBaseShips(0));
$template->assignByRef('Weapons',SmrWeapon::getAllWeapons(0));

$template->assign('EditDummysLink',SmrSession::get_new_href(create_container('skeleton.php','edit_dummys.php')));

$dummyPlayer =& DummyPlayer::getCachedDummyPlayer($_REQUEST['dummy_name']);
$dummyShip =& $dummyPlayer->getShip();

if(isset($_REQUEST['save_dummy'])) {
	$dummyPlayer->setPlayerName($_REQUEST['dummy_name']);
	$dummyPlayer->setExperience($_REQUEST['level']);
	$dummyPlayer->setShipTypeID($_REQUEST['ship_id']);
	$dummyShip->regenerate($dummyPlayer);
	if(isset($_REQUEST['weapons']) && is_array($_REQUEST['weapons'])) {
		$dummyShip->removeAllWeapons();
		foreach($_REQUEST['weapons'] as $weaponTypeID) {
			if($weaponTypeID!=0) {
				$dummyShip->addWeapon($weaponTypeID);
			}
		}
	}
	$dummyPlayer->cacheDummyPlayer();
}


$template->assignByRef('DummyPlayer',$dummyPlayer);
$template->assignByRef('DummyShip',$dummyShip);
$template->assignByRef('ShipWeapons',$dummyShip->getWeapons());
$template->assignByRef('Levels',Globals::getLevelRequirements());



$template->assign('DummyNames', DummyPlayer::getDummyPlayerNames());
?>