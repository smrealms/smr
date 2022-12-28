<?php declare(strict_types=1);

namespace Smr\Pages\Player\BetaFunctions;

use AbstractSmrPlayer;
use Smr\Page\PlayerPageProcessor;

abstract class BetaFunctionsPageProcessor extends PlayerPageProcessor {

	abstract protected function buildBetaFunctionsProcessor(AbstractSmrPlayer $player): void;

	public function build(AbstractSmrPlayer $player): never {
		$this->buildBetaFunctionsProcessor($player);

		$container = new BetaFunctions();
		$container->go();
	}

}
