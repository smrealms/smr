<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Combat\Results\Full\TraderFullCombatResults;
use Smr\Page\PlayerPage;
use Smr\Player;
use Smr\Template;

class AttackPlayer extends PlayerPage {

	public function __construct(
		private readonly TraderFullCombatResults $results,
		private readonly ?int $targetPlayerID,
		bool $playerDied,
	) {
		$this->skipRedirect = $playerDied;
	}

	public function build(Player $player, Template $template): void {
		if ($this->targetPlayerID !== null) {
			$target = Player::getPlayer($this->targetPlayerID);
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
