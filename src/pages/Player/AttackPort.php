<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Combat\Results\PortFullCombatResults;
use Smr\Page\PlayerPage;
use Smr\Player;
use Smr\Template;

class AttackPort extends PlayerPage {

	public function __construct(
		private readonly ?PortFullCombatResults $results = null,
		bool $playerDied = false,
	) {
		// If the player died, make sure they see combat results
		$this->skipRedirect = $playerDied;
	}

	public function build(Player $player, Template $template): void {
		$sector = $player->getSector();
		if (!$sector->hasPort()) {
			new CurrentSector(message: 'The port no longer exists!')->go();
		}
		$port = $sector->getPort();

		if ($this->results !== null) {
			$alreadyDestroyed = false;
			$creditedAttacker = false;
		} else {
			$alreadyDestroyed = true;
			$creditedAttacker = $port->isCreditedAttacker($player);
		}

		$template->pageRenderer = fn() => AttackPortRenderer::render(
			template: $template,
			FullPortCombatResults: $this->results,
			AlreadyDestroyed: $alreadyDestroyed,
			CreditedAttacker: $creditedAttacker,
			OverrideDeath: $player->isDead(),
			Port: $port,
			ThisPlayer: $player,
		);
	}

}
