<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\BuyerRestriction;
use Smr\Page\PlayerPageProcessor;
use Smr\ShipType;

class ShopShipProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly int $shipTypeID,
	) {}

	public function build(AbstractPlayer $player): never {
		$ship = $player->getShip();

		$shipTypeID = $this->shipTypeID;
		$newShipType = ShipType::get($shipTypeID);
		$cost = $ship->getCostToUpgrade($shipTypeID);

		$restriction = $newShipType->getRestriction();
		if (!$restriction->passes($player)) {
			$message = match ($restriction) {
				BuyerRestriction::Evil => 'Only members of the Underground can purchase this ship!',
				BuyerRestriction::Good => 'Only Federal deputies can purchase this ship!',
				default => 'You are not allowed to purchase this ship!',
			};
			create_error($message);
		}

		if ($newShipType->getRaceID() !== RACE_NEUTRAL && $player->getRaceID() !== $newShipType->getRaceID()) {
			create_error('You can\'t buy other race\'s ships!');
		}

		// do we have enough cash?
		if ($player->getCredits() < $cost) {
			create_error('You do not have enough cash to purchase this ship!');
		}

		// take the money from the user
		if ($cost > 0) {
			$player->decreaseCredits($cost);
		} else {
			$player->increaseCredits(-$cost);
		}

		// assign the new ship
		$ship->decloak();
		$ship->disableIllusion();
		$ship->setTypeID($shipTypeID);

		$player->log(LOG_TYPE_HARDWARE, 'Buys a ' . $newShipType->getName() . ' for ' . $cost . ' credits');

		$container = new CurrentSector();
		$container->go();
	}

}
