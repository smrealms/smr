<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use AbstractSmrPlayer;
use Smr\Page\PlayerPage;
use Smr\Template;
use SmrEnhancedWeaponEvent;
use SmrLocation;

class ShopWeapon extends PlayerPage {

	public string $file = 'shop_weapon.php';

	public function __construct(
		private readonly int $locationID
	) {}

	public function build(AbstractSmrPlayer $player, Template $template): void {
		$location = SmrLocation::getLocation($player->getGameID(), $this->locationID);
		$template->assign('PageTopic', $location->getName());
		$template->assign('ThisLocation', $location);

		$weaponsSold = $location->getWeaponsSold();

		// Check if any enhanced weapons are available
		$events = SmrEnhancedWeaponEvent::getShopEvents($player->getGameID(), $player->getSectorID(), $location->getTypeID());
		foreach ($events as $event) {
			$weapon = $event->getWeapon();
			$weaponsSold[$weapon->getWeaponTypeID()] = $weapon;
		}

		$template->assign('WeaponsSold', $weaponsSold);
	}

}
