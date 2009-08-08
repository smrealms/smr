<?php
// Why is this here? Is there a reason?
// sleep(1);
if ($var['action'] == 'Enable') {

	$ship->enableCloak();

}

elseif ($var['action'] == 'Disable') {

	$ship->decloak();

}

elseif ($var['action'] == 'Set Illusion')
{
	if (!is_numeric($_REQUEST['ship_id']) ||
		!is_numeric($_REQUEST['attack']) ||
		!is_numeric($_REQUEST['defense']))
		create_error('Numbers only please');

	$ship->setIllusion($_REQUEST['ship_id'], $_REQUEST['attack'], $_REQUEST['defense']);

}

elseif ($var['action'] == 'Disable Illusion') {

	$ship->disableIllusion();

}

$container = array();
$container['url'] = 'skeleton.php';
if ($player->isLandedOnPlanet())
	$container['body'] = 'planet_main.php';
else
	$container['body'] = 'current_sector.php';

forward($container);

?>