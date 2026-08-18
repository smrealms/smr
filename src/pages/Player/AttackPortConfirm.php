<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Page\PlayerPage;
use Smr\Player;
use Smr\Template;

class AttackPortConfirm extends PlayerPage {

	public function build(Player $player, Template $template): void {
		$sector = $player->getSector();

		if (!$sector->hasPort()) {
			create_error('This sector does not have a port.');
		}
		$port = $sector->getPort();

		if ($port->isBusted()) {
			(new AttackPort())->go();
		}

		$template->pageTopic = 'Port Raid: Sector #' . $port->getSectorID();

		$eligibleAttackers = $sector->getFightingTradersAgainstPort($player, $port, allEligible: true);

		$template->pageRenderer = fn() => AttackPortConfirmRenderer::render(
			PortAttackHREF: new AttackPortProcessor()->href(),
			Port: $port,
			VisiblePlayers: $eligibleAttackers,
			SectorPlayersLabel: 'Attackers',
			ThisShip: $player->getShip(),
			ThisAccount: $player->getAccount(),
			ThisPlanet: $sector->getPlanet(),
			ThisPlayer: $player,
		);
	}

}
