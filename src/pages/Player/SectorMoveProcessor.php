<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Globals;
use Smr\MovementType;
use Smr\Page\PlayerPageProcessor;
use Smr\Sector;
use Smr\SectorLock;

class SectorMoveProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly int $targetSectorID,
		private readonly CurrentSector|LocalMap $targetPage
	) {}

	public function build(AbstractPlayer $player): never {
		require_once(LIB . 'Default/sector_mines.inc.php');

		$sector = $player->getSector();

		if (!$player->getGame()->hasStarted()) {
			create_error('You cannot move until the game has started!');
		}

		if ($this->targetSectorID === $player->getSectorID()) {
			$this->targetPage->go();
		}

		if ($sector->getWarp() === $this->targetSectorID) {
			$movement = MovementType::Warp;
			$turns = TURNS_PER_WARP;
		} else {
			$movement = MovementType::Walk;
			$turns = TURNS_PER_SECTOR;
		}

		//allow hidden players (admins that don't play) to move without pinging, hitting mines, losing turns
		if (in_array($player->getAccountID(), Globals::getHiddenPlayers())) {
			//make them pop on CPL
			$player->updateLastCPLAction();
			$player->setSectorID($this->targetSectorID);
			$player->update();

			// get new sector object
			$sector = $player->getSector();
			$sector->markVisited($player);
			$this->targetPage->go();
		}

		// you can't move while on planet
		if ($player->isLandedOnPlanet()) {
			create_error('You can\'t activate your engine while you are on a planet!');
		}

		if ($player->getTurns() < $turns) {
			create_error('You don\'t have enough turns to move!');
		}

		if (!$sector->isLinked($this->targetSectorID)) {
			create_error('You cannot move to that sector!');
		}

		// If not moving to your "green sector", you might hit mines...
		if ($player->getLastSectorID() !== $this->targetSectorID) {
			// Update the "green sector"
			$player->setLastSectorID($this->targetSectorID);
			hit_sector_mines($player);
		}

		// log action
		$targetSector = Sector::getSector($player->getGameID(), $this->targetSectorID);
		$player->actionTaken('WalkSector', ['Sector' => $targetSector]);

		// send scout msg
		$sector->leavingSector($player, $movement);

		// Move the user around
		// TODO: (Must be done while holding both sector locks)
		$player->setSectorID($this->targetSectorID);
		$player->takeTurns($turns, $turns);
		$player->update();

		// We need to release the lock on our old sector
		$lock = SectorLock::getInstance();
		$lock->release();

		// We need a lock on the new sector so that more than one person isn't hitting the same mines
		$lock->acquireForPlayer($player);

		// get new sector object
		$sector = $player->getSector();

		//add that the player explored here if it hasnt been explored...for HoF
		if (!$sector->isVisited($player)) {
			$player->increaseExperience(EXPLORATION_EXPERIENCE);
			$player->increaseHOF(EXPLORATION_EXPERIENCE, ['Movement', 'Exploration Experience Gained'], HOF_ALLIANCE);
			$player->increaseHOF(1, ['Movement', 'Sectors Explored'], HOF_ALLIANCE);
		}
		// make current sector visible to him
		$sector->markVisited($player);

		// send scout msgs
		$sector->enteringSector($player, $movement);

		// If you bump into mines while entering the target sector...
		hit_sector_mines($player);

		// otherwise
		$this->targetPage->go();
	}

}
