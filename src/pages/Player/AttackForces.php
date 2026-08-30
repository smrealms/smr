<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Combat\Results\Full\ForceFullCombatResults;
use Smr\Force;
use Smr\Page\PlayerPage;
use Smr\Player;
use Smr\Template;

class AttackForces extends PlayerPage {

	public function __construct(
		private readonly int $ownerPlayerID,
		private readonly ForceFullCombatResults $results,
		bool $playerDied,
	) {
		// If the player died, make sure they see combat results
		$this->skipRedirect = $playerDied;
	}

	public function build(Player $player, Template $template): void {
		if ($this->ownerPlayerID > 0) {
			$target = Force::getForce($player->getGameID(), $player->getSectorID(), $this->ownerPlayerID);
		} else {
			$target = null;
		}

		$template->pageRenderer = fn() => AttackForcesRenderer::render(
			template: $template,
			FullForceCombatResults: $this->results,
			Target: $target,
			OverrideDeath: $player->isDead(),
			ThisShip: $player->getShip(),
		);
	}

}
