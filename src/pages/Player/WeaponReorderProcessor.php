<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use AbstractSmrPlayer;
use Smr\Page\PlayerPageProcessor;
use Smr\Request;

class WeaponReorderProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly int $weaponOrderID,
		private readonly string $direction
	) {}

	public function build(AbstractSmrPlayer $player): never {
		$ship = $player->getShip();

		if ($this->direction == 'Up') {
			$ship->moveWeaponUp($this->weaponOrderID);
		}

		if ($this->direction == 'Down') {
			$ship->moveWeaponDown($this->weaponOrderID);
		}

		if ($this->direction == 'Form') {
			$ship->setWeaponLocations(Request::getIntArray('weapon_reorder'));
		}

		(new WeaponReorder())->go();
	}

}
