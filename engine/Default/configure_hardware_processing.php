<?php
if ($var['action'] == 'Enable') {
	if($player->getTurns() < TURNS_TO_CLOAK) {
		create_error('You do not have enough turns to cloak.');
	}
	$player->takeTurns(TURNS_TO_CLOAK);
	$player->increaseHOF(TURNS_TO_CLOAK,array('Movement','Cloaking','Turns Used'), HOF_ALLIANCE);
	$player->increaseHOF(1,array('Movement','Cloaking','Times'), HOF_ALLIANCE);
	$ship->enableCloak();
}
elseif ($var['action'] == 'Disable') {
	$ship->decloak();
}

elseif ($var['action'] == 'Set Illusion') {
	if (!is_numeric($_REQUEST['ship_id']) ||
		!is_numeric($_REQUEST['attack']) ||
		!is_numeric($_REQUEST['defense'])) {
		create_error('Numbers only please!');
	}

	$ship->setIllusion($_REQUEST['ship_id'], $_REQUEST['attack'], $_REQUEST['defense']);
}
elseif ($var['action'] == 'Disable Illusion') {
	$ship->disableIllusion();
}

$container = create_container('skeleton.php', 'current_sector.php');
forward($container);
