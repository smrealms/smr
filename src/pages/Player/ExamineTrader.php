<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Page\PlayerPage;
use Smr\Player;
use Smr\Template;

class ExamineTrader extends PlayerPage {

	public function __construct(
		private readonly int $targetPlayerID,
	) {}

	public function build(Player $player, Template $template): void {
		// Get the player we're attacking
		$targetPlayer = Player::getPlayer($this->targetPlayerID);

		if ($targetPlayer->isDead()) {
			$msg = '<span class="red bold">ERROR:</span> Target already dead.';
			$container = new CurrentSector(message: $msg);
			$container->go();
		}

		$template->pageTopic = 'Examine Ship';

		$template->pageRenderer = fn() => ExamineTraderRenderer::render(
			TargetPlayer: $targetPlayer,
			NewbieKill: $targetPlayer->isNewbieCombatant($player),
			ThisPlayer: $player,
			ThisSector: $player->getSector(),
			ThisShip: $player->getShip(),
		);
	}

}
