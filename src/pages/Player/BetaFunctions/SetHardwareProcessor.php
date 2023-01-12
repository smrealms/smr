<?php declare(strict_types=1);

namespace Smr\Pages\Player\BetaFunctions;

use Smr\AbstractPlayer;
use Smr\Request;

class SetHardwareProcessor extends BetaFunctionsPageProcessor {

	public function buildBetaFunctionsProcessor(AbstractPlayer $player): void {
		$ship = $player->getShip();
		$type_hard = Request::getInt('type_hard');
		$amount_hard = Request::getInt('amount_hard');
		$ship->setHardware($type_hard, $amount_hard);
	}

}
