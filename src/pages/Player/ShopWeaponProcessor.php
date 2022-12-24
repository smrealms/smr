<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use AbstractSmrPlayer;
use Smr\BuyerRestriction;
use Smr\Page\PlayerPageProcessor;
use SmrLocation;
use SmrWeapon;

class ShopWeaponProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly int $locationID,
		private readonly SmrWeapon $weapon,
		private readonly ?int $sellOrderID = null
	) {}

	public function build(AbstractSmrPlayer $player): never {
		$ship = $player->getShip();

		if (!$player->getSector()->hasLocation($this->locationID)) {
			create_error('That location does not exist in this sector');
		}

		$weapon = $this->weapon;
		if ($this->sellOrderID === null) {
			// If here, we are buying
			$location = SmrLocation::getLocation($player->getGameID(), $this->locationID);
			if (!$location->isWeaponSold($weapon->getWeaponTypeID())) {
				create_error('We do not sell that weapon here!');
			}

			if ($weapon->getRaceID() != RACE_NEUTRAL && $player->getRelation($weapon->getRaceID()) < RELATIONS_PEACE) {
				create_error('We are at WAR!!! Do you really think I\'m gonna sell you that weapon?');
			}

			// do we have enough cash?
			if ($player->getCredits() < $weapon->getCost()) {
				create_error('You do not have enough cash to purchase this weapon!');
			}

			// can we load such a weapon (power_level)
			if (!$ship->checkPowerAvailable($weapon->getPowerLevel())) {
				create_error('Your ship doesn\'t have enough power to support that weapon!');
			}

			if ($ship->getOpenWeaponSlots() < 1) {
				create_error('You can\'t buy any more weapons!');
			}

			$restriction = $weapon->getBuyerRestriction();
			if (!$restriction->passes($player)) {
				$message = match ($restriction) {
					BuyerRestriction::Evil => 'Only members of the Underground can purchase this weapon!',
					BuyerRestriction::Good => 'Only Federal deputies can purchase this weapon!',
					BuyerRestriction::Newbie => 'Only newbie players can purchase this weapon!',
					default => 'You are not allowed to purchase this weapon!',
				};
				create_error($message);
			}

			if ($weapon->isUniqueType()) {
				foreach ($ship->getWeapons() as $shipWeapon) {
					if ($weapon->getWeaponTypeID() === $shipWeapon->getWeaponTypeID()) {
						create_error('This weapon is unique, and your ship already has one equipped!');
					}
				}
			}

			// take the money from the user
			$player->decreaseCredits($weapon->getCost());

			// add the weapon to the users ship
			$ship->addWeapon($weapon);
			$player->log(LOG_TYPE_HARDWARE, 'Player Buys a ' . $weapon->getName());
		} else {
			// mhh we wanna sell our weapon
			// give the money to the user
			$player->increaseCredits(IFloor($weapon->getCost() * WEAPON_REFUND_PERCENT));

			// take weapon
			$ship->removeWeapon($this->sellOrderID);

			$player->log(LOG_TYPE_HARDWARE, 'Player Sells a ' . $weapon->getName());
		}
		$container = new ShopWeapon($this->locationID);
		$container->go();
	}

}