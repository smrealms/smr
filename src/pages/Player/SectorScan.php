<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Sector;
use Smr\Template;

class SectorScan extends PlayerPage {

	public string $file = 'sector_scan.php';

	public function __construct(
		private readonly int $targetSectorID
	) {}

	public function build(AbstractPlayer $player, Template $template): void {
		$sector = $player->getSector();

		if (!$sector->isLinked($this->targetSectorID) && $sector->getSectorID() !== $this->targetSectorID) {
			create_error('You cannot scan a sector you are not linked to.');
		}

		// initialize vars
		$scanSector = Sector::getSector($player->getGameID(), $this->targetSectorID);

		$template->assign('PageTopic', 'Sector Scan of #' . $scanSector->getSectorID() . ' (' . $scanSector->getGalaxy()->getDisplayName() . ')');
		Menu::navigation($player);

		$friendly_forces = 0;
		$enemy_forces = 0;
		$friendly_vessel = 0;
		$enemy_vessel = 0;

		// iterate over all forces in the target sector
		foreach ($scanSector->getForces() as $scanSectorForces) {
			// decide if it's a friendly or enemy stack
			if ($player->sameAlliance($scanSectorForces->getOwner())) {
				$friendly_forces += $scanSectorForces->getMines() * 3 + $scanSectorForces->getCDs() * 2 + $scanSectorForces->getSDs();
			} else {
				$enemy_forces += $scanSectorForces->getMines() * 3 + $scanSectorForces->getCDs() * 2 + $scanSectorForces->getSDs();
			}
		}

		foreach ($scanSector->getOtherTraders($player) as $scanSectorPlayer) {
			$scanSectorShip = $scanSectorPlayer->getShip();

			// he's a friend if he's in our alliance (and we are not in a 0 alliance
			if ($player->traderMAPAlliance($scanSectorPlayer)) {
				$friendly_vessel += $scanSectorShip->getAttackRating();
			} else {
				$enemy_vessel += $scanSectorShip->getDefenseRating() * 10;
			}
		}

		$template->assign('FriendlyVessel', $friendly_vessel);
		$template->assign('FriendlyForces', $friendly_forces);
		$template->assign('EnemyVessel', $enemy_vessel);
		$template->assign('EnemyForces', $enemy_forces);

		// is it a warp or a normal move?
		if ($sector->getWarp() === $this->targetSectorID) {
			$turns = TURNS_PER_WARP;
		} else {
			$turns = TURNS_PER_SECTOR;
		}

		$template->assign('ScanSector', $scanSector);
		$template->assign('Turns', $turns);
	}

}
