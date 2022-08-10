<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use AbstractSmrPlayer;
use Globals;
use Smr\Page\PlayerPage;
use Smr\ShipClass;
use Smr\Template;
use SmrLocation;
use SmrShipType;

class ShopShip extends PlayerPage {

	public string $file = 'shop_ship.php';

	public function __construct(
		private readonly int $locationID,
		private readonly ?int $shipTypeID = null
	) {}

	public function build(AbstractSmrPlayer $player, Template $template): void {
		$location = SmrLocation::getLocation($player->getGameID(), $this->locationID);
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

		$shipsSoldHREF = [];
		foreach (array_keys($shipsSold) as $shipTypeID) {
			$container = new self($this->locationID, $shipTypeID);
			$shipsSoldHREF[$shipTypeID] = $container->href();
		}
		$template->assign('ShipsSoldHREF', $shipsSoldHREF);

		if ($this->shipTypeID !== null) {
			$ship = $player->getShip();
			$compareShip = SmrShipType::get($this->shipTypeID);

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

			$container = new ShopShipProcessor($this->shipTypeID);
			$template->assign('BuyHREF', $container->href());

			$template->assign('CompareShip', $compareShip);
			$template->assign('TradeInValue', $ship->getRefundValue());
			$template->assign('TotalCost', $ship->getCostToUpgrade($compareShip->getTypeID()));
		}
	}

}
