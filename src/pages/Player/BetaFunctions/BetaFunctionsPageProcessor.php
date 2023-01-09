<?php declare(strict_types=1);

namespace Smr\Pages\Player\BetaFunctions;

use Smr\AbstractPlayer;
use Smr\Page\PlayerPageProcessor;

abstract class BetaFunctionsPageProcessor extends PlayerPageProcessor {

	abstract protected function buildBetaFunctionsProcessor(AbstractPlayer $player): void;

	public function build(AbstractPlayer $player): never {
		$this->buildBetaFunctionsProcessor($player);

		$container = new BetaFunctions();
		$container->go();
	}

}
