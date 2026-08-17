<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\EnhancedWeaponEvent;
use Smr\Location;
use Smr\Page\PlayerPage;
use Smr\Player;
use Smr\Template;

class ShopWeapon extends PlayerPage {

	public function __construct(
		private readonly int $locationID,
	) {}

	public function build(Player $player, Template $template): void {
		$location = Location::getLocation($player->getGameID(), $this->locationID);
		$template->pageTopic = $location->getName();

		$weaponsSold = $location->getWeaponsSold();

		// Check if any enhanced weapons are available
		$events = EnhancedWeaponEvent::getShopEvents($player->getGameID(), $player->getSectorID(), $location->getTypeID());
		foreach ($events as $event) {
			$weapon = $event->getWeapon();
			$weaponsSold[$weapon->getWeaponTypeID()] = $weapon;
		}

		$template->pageRenderer = fn() => ShopWeaponRenderer::render(
			template: $template,
			ThisLocation: $location,
			WeaponsSold: $weaponsSold,
			ThisPlayer: $player,
			ThisShip: $player->getShip(),
		);
	}

}
