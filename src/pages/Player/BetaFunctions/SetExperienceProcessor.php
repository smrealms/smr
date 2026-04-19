<?php declare(strict_types=1);

namespace Smr\Pages\Player\BetaFunctions;

use Smr\Player;
use Smr\Request;

class SetExperienceProcessor extends BetaFunctionsPageProcessor {

	public function buildBetaFunctionsProcessor(Player $player): void {
		$exp = min(500000, Request::getInt('exp'));
		$player->setExperience($exp);
	}

}
