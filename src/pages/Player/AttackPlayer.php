<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Combat\Results\TraderFullCombatResults;
use Smr\Page\PlayerPage;
use Smr\Player;
use Smr\Template;

class AttackPlayer extends PlayerPage {

	public function __construct(
		private readonly TraderFullCombatResults $results,
		private readonly ?int $targetAccountID,
		bool $playerDied,
	) {
		$this->skipRedirect = $playerDied;
	}

	public function build(Player $player, Template $template): void {
		if ($this->targetAccountID !== null) {
			$target = Player::getPlayer($this->targetAccountID, $player->getGameID());
		} else {
			$target = null;
		}

		$template->pageRenderer = fn() => AttackPlayerRenderer::render(
			template: $template,
			TraderCombatResults: $this->results,
			Target: $target,
			OverrideDeath: $player->isDead(),
			ThisPlayer: $player,
		);
	}

}
