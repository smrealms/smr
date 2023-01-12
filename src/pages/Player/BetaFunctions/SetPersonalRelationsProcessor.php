<?php declare(strict_types=1);

namespace Smr\Pages\Player\BetaFunctions;

use Smr\AbstractPlayer;
use Smr\Request;

class SetPersonalRelationsProcessor extends BetaFunctionsPageProcessor {

	public function buildBetaFunctionsProcessor(AbstractPlayer $player): void {
		$amount = Request::getInt('amount');
		$race = Request::getInt('race');
		$player->setRelations($amount, $race);
	}

}
