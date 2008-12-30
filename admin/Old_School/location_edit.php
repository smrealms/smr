<?php

require_once(get_file_loc('SmrLocation.class.inc'));

$db = new SmrMySqlDatabase();
$db->query('SELECT location_type_id FROM location_type');

$locations = array();
while($db->nextRecord())
{
	$locations[$db->getField('location_type_id')] =& SmrLocation::getLocation($db->getField('location_type_id'));
}

$smarty->assign_by_ref('Locations',$locations);
?>