<?php declare(strict_types=1);

namespace Smr\Pages\Player\BetaFunctions;

use Smr\AbstractPlayer;
use Smr\Request;

class SetShipProcessor extends BetaFunctionsPageProcessor {

	public function buildBetaFunctionsProcessor(AbstractPlayer $player): void {
		$ship = $player->getShip();
		$shipTypeID = Request::getInt('ship_type_id');
		if ($shipTypeID <= 75 && $shipTypeID !== 68) {
			// assign the new ship
			$ship->decloak();
			$ship->disableIllusion();
			$ship->setTypeID($shipTypeID);
			$ship->setHardwareToMax();
		}
	}

}
