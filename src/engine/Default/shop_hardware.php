<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();

if (!$player->getSector()->hasLocation($var['LocationID'])) {
	create_error('That location does not exist in this sector');
}

$template->assign('PageTopic', 'Hardware Shop');

$location = SmrLocation::getLocation($player->getGameID(), $var['LocationID']);
if ($location->isHardwareSold()) {
	$hardwareSold = $location->getHardwareSold();
	foreach ($hardwareSold as $hardwareTypeID => $hardware) {
		$container = Page::create('shop_hardware_processing.php');
		$container->addVar('LocationID');
		$container['hardware_id'] = $hardwareTypeID;
		$hardwareSold[$hardwareTypeID]['HREF'] = $container->href();
	}
	$template->assign('HardwareSold', $hardwareSold);
}
