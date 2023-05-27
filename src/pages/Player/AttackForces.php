<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Force;
use Smr\Page\PlayerPage;
use Smr\Template;

class AttackForces extends PlayerPage {

	public string $file = 'forces_attack.php';

	/**
	 * @param array{Attackers: ForceAttackerCombatResults, Forces: ForceCombatResults, Forced: bool} $results
	 */
	public function __construct(
		private readonly int $ownerAccountID,
		private readonly array $results,
		bool $playerDied,
	) {
		// If the player died, make sure they see combat results
		$this->skipRedirect = $playerDied;
	}

	public function build(AbstractPlayer $player, Template $template): void {
		$template->assign('FullForceCombatResults', $this->results);

		if ($this->ownerAccountID > 0) {
			$template->assign('Target', Force::getForce($player->getGameID(), $player->getSectorID(), $this->ownerAccountID));
		}

		$template->assign('OverrideDeath', $player->isDead());
	}

}
