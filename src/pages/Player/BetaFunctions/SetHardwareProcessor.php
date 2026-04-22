<?php declare(strict_types=1);

namespace Smr\Pages\Player\BetaFunctions;

use Smr\Player;
use Smr\Request;

class SetHardwareProcessor extends BetaFunctionsPageProcessor {

	public function buildBetaFunctionsProcessor(Player $player): void {
		$ship = $player->getShip();
		$type_hard = Request::getInt('type_hard');
		$max_hard = $ship->getType()->getMaxHardware($type_hard);
		$amount_hard = min(Request::getInt('amount_hard'), $max_hard);
		$ship->setHardware($type_hard, $amount_hard);
	}

}
