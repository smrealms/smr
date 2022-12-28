<?php declare(strict_types=1);

namespace Smr\Pages\Player\BetaFunctions;

use AbstractSmrPlayer;
use Smr\Request;

class SetExperienceProcessor extends BetaFunctionsPageProcessor {

	public function buildBetaFunctionsProcessor(AbstractSmrPlayer $player): void {
		$exp = min(500000, Request::getInt('exp'));
		$player->setExperience($exp);
	}

}
