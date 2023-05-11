<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Exceptions\PathNotFound;
use Smr\MovementType;
use Smr\Page\PlayerPageProcessor;
use Smr\Plotter;
use Smr\Request;
use Smr\Sector;
use Smr\SectorLock;

class SectorJumpProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly ?int $targetSectorID = null
	) {}

	public function build(AbstractPlayer $player): never {
		$sector = $player->getSector();

		if (!$player->getGame()->hasStarted()) {
			create_error('You cannot move until the game has started!');
		}

		$target = $this->targetSectorID ?? Request::getInt('target');

		//allow hidden players (admins that don't play) to move without pinging, hitting mines, losing turns
		if ($player->isObserver()) {
			$player->setSectorID($target);
			$player->update();
			$sector->markVisited($player);
			(new CurrentSector())->go();
		}

		// you can't move while on planet
		if ($player->isLandedOnPlanet()) {
			create_error('You are on a planet! You must launch first!');
		}

		if ($player->getSectorID() === $target) {
			create_error('Hmmmm...if ' . $player->getSectorID() . '=' . $target . ' then that means...YOU\'RE ALREADY THERE! *cough*you\'re real smart*cough*');
		}

		if (!Sector::sectorExists($player->getGameID(), $target)) {
			create_error('The target sector doesn\'t exist!');
		}

		// If the Calculate Turn Cost button was pressed
		if (Request::get('action', '') === 'Calculate Turn Cost') {
			$container = new SectorJumpCalculate($target);
			$container->go();
		}

		if ($sector->hasForces()) {
			foreach ($sector->getForces() as $forces) {
				if ($forces->hasMines() && !$player->forceNAPAlliance($forces->getOwner())) {
					create_error('You cannot jump when there are hostile mines in the sector!');
				}
			}
		}

		// create sector object for target sector
		$targetSector = Sector::getSector($player->getGameID(), $target);

		try {
			$jumpInfo = $player->getJumpInfo($targetSector);
		} catch (PathNotFound) {
			create_error('Unable to plot from ' . $player->getSectorID() . ' to ' . $targetSector->getSectorID());
		}
		$turnsToJump = $jumpInfo['turn_cost'];
		$maxMisjump = $jumpInfo['max_misjump'];

		// check for turns
		if ($player->getTurns() < $turnsToJump) {
			create_error('You don\'t have enough turns for that jump!');
		}

		// send scout msg
		$sector->leavingSector($player, MovementType::Jump);

		// Move the user around
		// TODO: (Must be done while holding both sector locks)
		$misjump = rand(0, $maxMisjump);
		if ($misjump > 0) { // we missed the sector
			$paths = Plotter::findDistanceToX('Distance', $targetSector, false, null, null, $misjump);

			// Group candidate sectors by distance from the target
			$distances = [0 => [$targetSector->getSectorID()]]; // fallback to target
			foreach ($paths as $sectorID => $path) {
				$distances[$path->getDistance()][] = $sectorID;
			}

			// Try to find a valid sector, reduce misjump if none
			while (!isset($distances[$misjump])) {
				$misjump--;
			}

			$misjumpSector = array_rand_value($distances[$misjump]);
			$player->setSectorID($misjumpSector);
			unset($distances);
		} else { // we hit it. exactly
			$player->setSectorID($targetSector->getSectorID());
		}
		$player->takeTurns($turnsToJump, $turnsToJump);

		// log action
		$player->log(LOG_TYPE_MOVEMENT, 'Jumps to sector: ' . $target . ' but hits: ' . $player->getSectorID());

		$player->update();

		// We need to release the lock on our old sector
		$lock = SectorLock::getInstance();
		$lock->release();

		// We need a lock on the new sector so that more than one person isn't hitting the same mines
		$lock->acquireForPlayer($player);

		// get new sector object
		$sector = $player->getSector();

		// make current sector visible to him
		$sector->markVisited($player);

		// send scout msg
		$sector->enteringSector($player, MovementType::Jump);

		// If the new sector has mines...
		require_once(LIB . 'Default/sector_mines.inc.php');
		hit_sector_mines($player);

		(new CurrentSector())->go();
	}

}
