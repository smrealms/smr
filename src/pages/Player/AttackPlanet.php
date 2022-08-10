<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use AbstractSmrPlayer;
use Smr\Page\PlayerPage;
use Smr\Template;
use SmrPlanet;

class AttackPlanet extends PlayerPage {

	public string $file = 'planet_attack.php';

	/**
	 * @param array<mixed> $results
	 */
	public function __construct(
		private readonly int $sectorID,
		private readonly array $results,
		bool $playerDied
	) {
		// If the player died, make sure they see combat results
		$this->skipRedirect = $playerDied;
	}

	public function build(AbstractSmrPlayer $player, Template $template): void {
		$template->assign('FullPlanetCombatResults', $this->results);
		$template->assign('MinimalDisplay', false);
		$template->assign('OverrideDeath', $player->isDead());
		$template->assign('Planet', SmrPlanet::getPlanet($player->getGameID(), $this->sectorID));
	}

}
