<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Combat\Results\Full\PortFullCombatResults;
use Smr\Page\PlayerPage;
use Smr\Player;
use Smr\Sector;
use Smr\Template;

class AttackPort extends PlayerPage {

	public function __construct(
		private readonly int $sectorID,
		private readonly ?PortFullCombatResults $results = null,
		bool $playerDied = false,
	) {
		// If the player died, make sure they see combat results
		$this->skipRedirect = $playerDied;
	}

	public function build(Player $player, Template $template): void {
		// Either player or port may no longer be in sector
		$sector = Sector::getSector($player->getGameID(), $this->sectorID);
		$port = $sector->getPortOrNull();
		if ($port === null) {
			new CurrentSector(message: 'The port no longer exists!')->go();
		}

		// Display port already destroyed page content if port is busted and we don't
		// have existing combat results to display.
		$hasResults = $this->results !== null;
		$alreadyDestroyed = $port->isBusted() && !$hasResults;

		// If port is busted and we do have combat results, it means the player has
		// attacked the port, and we can skip the credited check as an optimization.
		// (Note: credited attackers may not have been present for kill)
		$creditedAttacker = $port->isBusted() && ($hasResults || $port->isCreditedAttacker($player));

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
