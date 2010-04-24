<?php
// Why is this here? Is there a reason?
// sleep(1);
if ($var['action'] == 'Enable') {

	$ship->enable_cloak();

}

elseif ($var['action'] == 'Disable') {

	$ship->disable_cloak();

}

elseif ($var['action'] == 'Set Illusion') {

	if (!is_numeric($_REQUEST['ship_id']) ||
		!is_numeric($_REQUEST['attack']) ||
		!is_numeric($_REQUEST['defense']))
		create_error('Numbers only please');

	$ship->set_illusion($_REQUEST['ship_id'], $_REQUEST['attack'], $_REQUEST['defense']);

}

elseif ($var['action'] == 'Disable Illusion') {

	$ship->disable_illusion();

}

$container = array();
$container['url'] = 'skeleton.php';
if ($player->land_on_planet == 'TRUE')
	$container['body'] = 'planet_main.php';
else
	$container['body'] = 'current_sector.php';

forward($container);

?>