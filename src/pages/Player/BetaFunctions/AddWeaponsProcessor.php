<?php declare(strict_types=1);

namespace Smr\Pages\Player\BetaFunctions;

use AbstractSmrPlayer;
use Smr\Request;
use SmrWeapon;

class AddWeaponsProcessor extends BetaFunctionsPageProcessor {

	public function buildBetaFunctionsProcessor(AbstractSmrPlayer $player): void {
		$ship = $player->getShip();
		$weapon = SmrWeapon::getWeapon(Request::getInt('weapon_id'));
		$amount = Request::getInt('amount');
		for ($i = 0; $i < $amount; $i++) {
			$ship->addWeapon($weapon);
		}
	}

}
