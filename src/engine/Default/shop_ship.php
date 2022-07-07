<?php declare(strict_types=1);

use Smr\ShipClass;

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();

$location = SmrLocation::getLocation($var['LocationID']);
$template->assign('PageTopic', $location->getName());

$shipsSold = $location->getShipsSold();

// Move any locked ships to a separate array so that they can't be bought.
// Note: Only Raider-class ships and PSF can be locked.
$timeUntilUnlock = $player->getGame()->timeUntilShipUnlock();
$shipsUnavailable = [];
foreach ($shipsSold as $shipTypeID => $shipType) {
	if ($timeUntilUnlock > 0 && ($shipType->getClass() === ShipClass::Raider || $shipType->getTypeID() === SHIP_TYPE_PLANETARY_SUPER_FREIGHTER)) {
		$shipsUnavailable[] = [
			'Name' => $shipType->getName(),
			'TimeUntilUnlock' => $timeUntilUnlock,
		];
		unset($shipsSold[$shipTypeID]); // remove from available ships
	}
}
$template->assign('ShipsUnavailable', $shipsUnavailable);
$template->assign('ShipsSold', $shipsSold);

$container = Page::create('shop_ship.php');
$container->addVar('LocationID');
$shipsSoldHREF = [];
foreach (array_keys($shipsSold) as $shipTypeID) {
	$container['ship_type_id'] = $shipTypeID;
	$shipsSoldHREF[$shipTypeID] = $container->href();
}
$template->assign('ShipsSoldHREF', $shipsSoldHREF);

if (isset($var['ship_type_id'])) {
	$ship = $player->getShip();
	$compareShip = SmrShipType::get($var['ship_type_id']);

	$shipDiffs = [];
	foreach (Globals::getHardwareTypes() as $hardwareTypeID => $hardware) {
		$shipDiffs[$hardware['Name']] = [
			'Old' => $ship->getType()->getMaxHardware($hardwareTypeID),
			'New' => $compareShip->getMaxHardware($hardwareTypeID),
		];
	}
	$shipDiffs['Hardpoints'] = [
		'Old' => $ship->getHardpoints(),
		'New' => $compareShip->getHardpoints(),
	];
	$shipDiffs['Speed'] = [
		'Old' => $ship->getRealSpeed(),
		'New' => $compareShip->getSpeed() * $player->getGame()->getGameSpeed(),
	];
	$shipDiffs['Turns'] = [
		'Old' => $player->getTurns(),
		'New' => round($player->getTurns() * $compareShip->getSpeed() / $ship->getType()->getSpeed()),
	];
	$template->assign('ShipDiffs', $shipDiffs);

	$container = Page::create('shop_ship_processing.php');
	$container->addVar('LocationID');
	$container->addVar('ship_type_id');
	$template->assign('BuyHREF', $container->href());

	$template->assign('CompareShip', $compareShip);
	$template->assign('TradeInValue', $ship->getRefundValue());
	$template->assign('TotalCost', $ship->getCostToUpgrade($compareShip->getTypeID()));
}
