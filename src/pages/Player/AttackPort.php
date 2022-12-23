<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use AbstractSmrPlayer;
use Smr\Page\PlayerPage;
use Smr\Template;

class AttackPort extends PlayerPage {

	public string $file = 'port_attack.php';

	/**
	 * @param array<mixed> $results
	 */
	public function __construct(
		private readonly ?array $results = null,
		bool $playerDied = false
	) {
		// If the player died, make sure they see combat results
		$this->skipRedirect = $playerDied;
	}

	public function build(AbstractSmrPlayer $player, Template $template): void {
		$port = $player->getSector()->getPort();

		if ($this->results !== null) {
			$template->assign('FullPortCombatResults', $this->results);
			$template->assign('AlreadyDestroyed', false);
			$template->assign('CreditedAttacker', true);
		} else {
			$template->assign('AlreadyDestroyed', true);
			$template->assign('CreditedAttacker', in_array($player, $port->getAttackersToCredit()));
		}
		$template->assign('MinimalDisplay', false);

		$template->assign('OverrideDeath', $player->isDead());
		$template->assign('Port', $port);
	}

}
