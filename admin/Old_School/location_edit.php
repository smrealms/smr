<?php

$smarty->assign('ViewAllLocationsLink',SmrSession::get_new_href(create_container('skeleton.php','location_edit.php')));

require_once(get_file_loc('SmrLocation.class.inc'));

if(isset($var['location_type_id']))
{
	$location =& SmrLocation::getLocation($var['location_type_id']);
	if(isset($_REQUEST['save']))
	{
		if($_REQUEST['add_ship_id']!=0)
			$location->addShipSold($_REQUEST['add_ship_id']);
		if($_REQUEST['add_weapon_id']!=0)
			$location->addWeaponSold($_REQUEST['add_weapon_id']);
		if($_REQUEST['add_hardware_id']!=0)
			$location->addHardwareSold($_REQUEST['add_hardware_id']);
		$location->setFed(isset($_REQUEST['fed']));
		$location->setBar(isset($_REQUEST['bar']));
		$location->setBank(isset($_REQUEST['bank']));
		$location->setHQ(isset($_REQUEST['hq']));
		$location->setUG(isset($_REQUEST['ug']));
	}
	
	
	$smarty->assign_by_ref('Location',$location);
	$smarty->assign_by_ref('Ships',AbstractSmrShip::getAllBaseShips($var['game_type_id']));
	$smarty->assign_by_ref('Weapons',SmrWeapon::getAllWeapons($var['game_type_id']));
	
	
	$db->query('SELECT * FROM hardware_type');
	$hardware = array();
	while($db->next_record())
	{
		$hardware[$db->f('hardware_type_id')] = array('ID' => $db->f('hardware_type_id'),
														'Name' => $db->f('hardware_name'));
	}
	$smarty->assign_by_ref('AllHardware',$hardware);
}
else
{
	$db->query('SELECT location_type_id FROM location_type');
	$locations = array();
	while($db->next_record())
	{
		$locations[$db->f('location_type_id')] =& SmrLocation::getLocation($db->f('location_type_id'));
	}
	
	$smarty->assign_by_ref('Locations',$locations);
}
?>