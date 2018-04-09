<?php
$location =& SmrLocation::getLocation($var['LocationID']);
if ($_REQUEST['action'] == 'Become a deputy') {
	if(!$location->isHQ()) {
		create_error('You have to be at a HQ to become a deputy.');
	}
	$player->setAlignment(150);
	$player->update();
}
elseif ($_REQUEST['action'] == 'Become a gang member') {
	if(!$location->isUG()) {
		create_error('You have to be at a HQ to become a deputy.');
	}
	$player->setAlignment(-150);
	$player->update();
}

forward(create_container('skeleton.php', 'current_sector.php'));
