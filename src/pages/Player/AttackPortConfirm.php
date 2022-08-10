<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use AbstractSmrPlayer;
use Smr\Page\PlayerPage;
use Smr\Template;

class AttackPortConfirm extends PlayerPage {

	public string $file = 'port_attack_warning.php';

	public function build(AbstractSmrPlayer $player, Template $template): void {
		$sector = $player->getSector();

		if (!$sector->hasPort()) {
			create_error('This sector does not have a port.');
		}
		$port = $sector->getPort();

		if ($port->isDestroyed()) {
			(new AttackPort())->go();
		}

		$template->assign('PageTopic', 'Port Raid');

		$template->assign('PortAttackHREF', (new AttackPortProcessor())->href());
		$template->assign('Port', $port);

		$eligibleAttackers = $sector->getFightingTradersAgainstPort($player, $port, allEligible: true);
		$template->assign('VisiblePlayers', $eligibleAttackers);
		$template->assign('SectorPlayersLabel', 'Attackers');
	}

}
