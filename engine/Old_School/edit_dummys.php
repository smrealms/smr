<?php
$smarty->assign('PageTopic','Edit Dummys');

require_once(get_file_loc('DummyPlayer.class.inc'));
require_once(get_file_loc('DummyShip.class.inc'));
require_once(get_file_loc('SmrWeapon.class.inc'));
//TODO add game type id

echo memory_get_usage() . '<br />';

$smarty->assign('EditDummysLink',SmrSession::get_new_href(create_container('skeleton.php','edit_dummys.php')));

echo memory_get_usage() . '<br />';
$dummyPlayer =& DummyPlayer::getCachedDummyPlayer($_REQUEST['dummy_name']);
echo memory_get_usage() . '<br />';
$dummyShip =& $dummyPlayer->getShip();

echo memory_get_usage() . '<br />';
if(isset($_REQUEST['save_dummy']))
{
	$dummyPlayer->setPlayerName($_REQUEST['dummy_name']);
	$dummyPlayer->setExperience($_REQUEST['level']);
	$dummyPlayer->setShipTypeID($_REQUEST['ship_id']);
echo memory_get_usage() . '<br />';
	$dummyShip->regenerate($dummyPlayer);
echo memory_get_usage() . '<br />';
	$dummyShip->removeAllWeapons();
echo memory_get_usage() . '<br />';
	foreach($_REQUEST['weapons'] as $weaponTypeID)
	{
		$dummyShip->addWeapon($weaponTypeID);
	}
echo memory_get_usage() . '<br />';
	$dummyPlayer->cacheDummyPlayer();
echo memory_get_usage() . '<br />';
}


$smarty->assign_by_ref('DummyPlayer',$dummyPlayer);
$smarty->assign_by_ref('DummyShip',$dummyShip);
$smarty->assign_by_ref('ShipWeapons',$dummyShip->getWeapons());
$smarty->assign_by_ref('Levels',Globals::getLevelRequirements());



$smarty->assign('DummyNames', DummyPlayer::getDummyPlayerNames());

$smarty->assign_by_ref('BaseShips',AbstractSmrShip::getAllBaseShips(0));
$smarty->assign_by_ref('Weapons',SmrWeapon::getAllWeapons(0));
?>