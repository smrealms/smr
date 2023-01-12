<?php declare(strict_types=1);

namespace Smr\Pages\Player\BetaFunctions;

use Smr\AbstractPlayer;
use Smr\Request;

class SetAlignmentProcessor extends BetaFunctionsPageProcessor {

	public function buildBetaFunctionsProcessor(AbstractPlayer $player): void {
		$align = max(-500, min(500, Request::getInt('align')));
		$player->setAlignment($align);
	}

}
