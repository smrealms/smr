<?php declare(strict_types=1);

namespace Smr\Pages\Player\BetaFunctions;

use Smr\Page\PlayerPageProcessor;
use Smr\Player;

abstract class BetaFunctionsPageProcessor extends PlayerPageProcessor {

	abstract protected function buildBetaFunctionsProcessor(Player $player): void;

	public function build(Player $player): never {
		$this->buildBetaFunctionsProcessor($player);

		$container = new BetaFunctions();
		$container->go();
	}

}
