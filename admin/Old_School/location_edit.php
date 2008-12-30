<?php

require_once(get_file_loc('SmrLocation.class.inc'));

$db->query('SELECT location_type_id FROM location_type');

$locations = array();
while($db->next_record())
{
	$locations[$db->f('location_type_id')] =& SmrLocation::getLocation($db->f('location_type_id'));
}

$smarty->assign_by_ref('Locations',$locations);
?>