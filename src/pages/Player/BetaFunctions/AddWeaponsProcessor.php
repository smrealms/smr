<?php declare(strict_types=1);

namespace Smr\Pages\Player\BetaFunctions;

use Smr\AbstractPlayer;
use Smr\Combat\Weapon\Weapon;
use Smr\Request;

class AddWeaponsProcessor extends BetaFunctionsPageProcessor {

	public function buildBetaFunctionsProcessor(AbstractPlayer $player): void {
		$ship = $player->getShip();
		$weapon = Weapon::getWeapon(Request::getInt('weapon_id'));
		$amount = Request::getInt('amount');
		for ($i = 0; $i < $amount; $i++) {
			$ship->addWeapon($weapon);
		}
	}

}
