<?php

$template->assign('ViewAllLocationsLink',SmrSession::getNewHREF(create_container('skeleton.php','location_edit.php')));

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
		if(isset($_REQUEST['remove_ships']) && is_array($_REQUEST['remove_ships']))
			foreach($_REQUEST['remove_ships'] as $shipTypeID)
				$location->removeShipSold($shipTypeID);
		if(isset($_REQUEST['remove_weapons']) && is_array($_REQUEST['remove_weapons']))
			foreach($_REQUEST['remove_weapons'] as $weaponTypeID)
				$location->removeWeaponSold($weaponTypeID);
		if(isset($_REQUEST['remove_hardware']) && is_array($_REQUEST['remove_hardware']))
			foreach($_REQUEST['remove_hardware'] as $hardwareTypeID)
				$location->removeHardwareSold($hardwareTypeID);

		$location->setFed(isset($_REQUEST['fed']));
		$location->setBar(isset($_REQUEST['bar']));
		$location->setBank(isset($_REQUEST['bank']));
		$location->setHQ(isset($_REQUEST['hq']));
		$location->setUG(isset($_REQUEST['ug']));
	}
	
	
	$template->assignByRef('Location',$location);
	$template->assignByRef('Ships',AbstractSmrShip::getAllBaseShips($var['game_type_id']));
	$template->assignByRef('Weapons',SmrWeapon::getAllWeapons($var['game_type_id']));
	
	
	$db->query('SELECT * FROM hardware_type');
	$hardware = array();
	while($db->nextRecord())
	{
		$hardware[$db->getField('hardware_type_id')] = array('ID' => $db->getField('hardware_type_id'),
														'Name' => $db->getField('hardware_name'));
	}
	$template->assignByRef('AllHardware',$hardware);
}
else
{
	$template->assignByRef('Locations',SmrLocation::getAllLocations());
}
?>