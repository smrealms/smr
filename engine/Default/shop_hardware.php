<?php

$template->assign('PageTopic','Hardware Shop');

if(!$player->getSector()->hasLocation($var['LocationID'])) {
	create_error('That location does not exist in this sector');
}

$location =& SmrLocation::getLocation($var['LocationID']);
if ($location->isHardwareSold()) {
	$hardwareSold = $location->getHardwareSold();
	foreach ($hardwareSold as $hardwareTypeID => $hardware) {
		$container = create_container('shop_hardware_processing.php');
		transfer('LocationID');
		$container['hardware_id'] = $hardwareTypeID;
		$hardwareSold[$hardwareTypeID]['HREF'] = SmrSession::getNewHREF($container);
	}
	$template->assign('HardwareSold', $hardwareSold);
}

?>