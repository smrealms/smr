<?php declare(strict_types=1);

namespace Smr\Pages\Player\BetaFunctions;

use Smr\Player;
use Smr\Request;

class SetShipProcessor extends BetaFunctionsPageProcessor {

	public function buildBetaFunctionsProcessor(Player $player): void {
		$ship = $player->getShip();
		$shipTypeID = Request::getInt('ship_type_id');
		if ($shipTypeID <= 75 && $shipTypeID !== 68) {
			// assign the new ship
			$ship->setTypeID($shipTypeID);
			$ship->setHardwareToMax();
		}
	}

}
