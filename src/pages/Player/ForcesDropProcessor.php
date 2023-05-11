<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Force;
use Smr\Globals;
use Smr\Page\PlayerPageProcessor;
use Smr\Request;

class ForcesDropProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly int $ownerAccountID,
		private readonly ?int $dropMines = null,
		private readonly ?int $takeMines = null,
		private readonly ?int $dropCDs = null,
		private readonly ?int $takeCDs = null,
		private readonly ?int $dropSDs = null,
		private readonly ?int $takeSDs = null,
		private readonly ?string $referrer = null
	) {}

	public function build(AbstractPlayer $player): never {
		$ship = $player->getShip();

		if ($player->getNewbieTurns() > 0) {
			create_error('You can\'t take/drop forces under newbie protection!');
		}

		if ($player->isLandedOnPlanet()) {
			create_error('You must first launch to drop forces!');
		}

		if ($player->getSector()->hasLocation()) {
			create_error('You can\'t drop forces in a sector with a location!');
		}

		// take either from container or request, prefer container
		$drop_mines = $this->dropMines ?? Request::getInt('drop_mines', 0);
		$take_mines = $this->takeMines ?? Request::getInt('take_mines', 0);
		$drop_combat_drones = $this->dropCDs ?? Request::getInt('drop_combat_drones', 0);
		$take_combat_drones = $this->takeCDs ?? Request::getInt('take_combat_drones', 0);
		$drop_scout_drones = $this->dropSDs ?? Request::getInt('drop_scout_drones', 0);
		$take_scout_drones = $this->takeSDs ?? Request::getInt('take_scout_drones', 0);

		// so how many forces do we take/add per type?
		$change_mines = $drop_mines - $take_mines;
		$change_combat_drones = $drop_combat_drones - $take_combat_drones;
		$change_scout_drones = $drop_scout_drones - $take_scout_drones;

		$forces = Force::getForce($player->getGameID(), $player->getSectorID(), $this->ownerAccountID);

		// check max on that stack
		$at_max = false;
		if ($forces->getMines() + $change_mines > Force::MAX_MINES) {
			$change_mines = Force::MAX_MINES - $forces->getMines();
			$at_max = $change_mines === 0;
		}

		if ($forces->getCDs() + $change_combat_drones > Force::MAX_CDS) {
			$change_combat_drones = Force::MAX_CDS - $forces->getCDs();
			$at_max = $change_combat_drones === 0;
		}

		if ($forces->getSDs() + $change_scout_drones > Force::MAX_SDS) {
			$change_scout_drones = Force::MAX_SDS - $forces->getSDs();
			$at_max = $change_scout_drones === 0;
		}

		// Check if the delta is 0 after applying the caps, in case by applying the caps we actually changed it to 0.
		if ($change_mines === 0 && $change_combat_drones === 0 && $change_scout_drones === 0) {
			if ($at_max) {
				// If no forces added only because the stack is full
				create_error('This stack can\'t hold any more of those forces!');
			} else {
				// If drop == take
				create_error('You want to add/remove 0 forces?');
			}
		}

		// NOTE: we do all error checking first, because any forces we remove from
		// the ship will vanish if we hit an error afterwards. This is because we
		// don't update the force stack expire time until the end of this script.
		// Force stacks without an updated expire time are automatically removed.
		//
		// We don't make the expire time update part of every force change internally
		// because those functions are used to remove forces via combat (which isn't
		// supposed to update the expire time).

		if ($change_combat_drones !== 0) {
			// we can't take more forces than are in sector
			if ($forces->getCDs() + $change_combat_drones < 0) {
				create_error('You can\'t take more combat drones than are on this stack!');
			}

			if ($ship->getCDs() - $change_combat_drones > $ship->getMaxCDs()) {
				create_error('Your ships supports no more than ' . $ship->getMaxCDs() . ' combat drones!');
			}

			if ($ship->getCDs() - $change_combat_drones < 0) {
				create_error('You can\'t drop more combat drones than you carry!');
			}
		}

		if ($change_scout_drones !== 0) {
			// we can't take more forces than are in sector
			if ($forces->getSDs() + $change_scout_drones < 0) {
				create_error('You can\'t take more scout drones than are on this stack!');
			}

			if ($ship->getSDs() - $change_scout_drones > $ship->getMaxSDs()) {
				create_error('Your ships supports no more than ' . $ship->getMaxSDs() . ' scout drones!');
			}

			if ($ship->getSDs() - $change_scout_drones < 0) {
				create_error('You can\'t drop more scout drones than you carry!');
			}
		}

		if ($change_mines !== 0) {
			// we can't take more forces than are in sector
			if ($forces->getMines() + $change_mines < 0) {
				create_error('You can\'t take more mines than are on this stack!');
			}

			if ($ship->getMines() - $change_mines > $ship->getMaxMines()) {
				create_error('Your ships supports no more than ' . $ship->getMaxMines() . ' mines!');
			}

			if ($ship->getMines() - $change_mines < 0) {
				create_error('You can\'t drop more mines than you carry!');
			}
		}

		// All error checking is done, so now update the ship/force

		if ($change_combat_drones !== 0) {
			if ($change_combat_drones > 0) {
				$ship->decreaseCDs($change_combat_drones);
				$forces->addCDs($change_combat_drones);
			} else {
				$ship->increaseCDs(-$change_combat_drones);
				$forces->takeCDs(-$change_combat_drones);
			}
		}

		if ($change_scout_drones !== 0) {
			if ($change_scout_drones > 0) {
				$ship->decreaseSDs($change_scout_drones);
				$forces->addSDs($change_scout_drones);
			} else {
				$ship->increaseSDs(-$change_scout_drones);
				$forces->takeSDs(-$change_scout_drones);
			}
		}

		if ($change_mines !== 0) {
			if ($change_mines > 0) {
				$ship->decreaseMines($change_mines);
				$forces->addMines($change_mines);
				if ($ship->isCloaked()) {
					$ship->decloak();
					$player->giveTurns(1);
				}
			} else {
				$ship->increaseMines(-$change_mines);
				$forces->takeMines(-$change_mines);
			}
		}

		// message to send out
		if ($forces->getOwnerID() !== $player->getAccountID() && $forces->getOwner()->isForceDropMessages()) {
			$msgParts = [];
			if ($change_mines > 0) {
				$msgParts[] = 'added ' . pluralise($change_mines, 'mine');
			} elseif ($change_mines < 0) {
				$msgParts[] = 'removed ' . pluralise(abs($change_mines), 'mine');
			}

			if ($change_combat_drones > 0) {
				$msgParts[] = ($change_mines <= 0 ? 'added ' : '') . pluralise($change_combat_drones, 'combat drone');
			} elseif ($change_combat_drones < 0) {
				$msgParts[] = ($change_mines >= 0 ? 'removed ' : '') . pluralise(abs($change_combat_drones), 'combat drone');
			}

			if ($change_scout_drones > 0) {
				$scout_drones_message = '';
				if ($change_combat_drones < 0 || ($change_combat_drones === 0 && $change_mines <= 0)) {
					$scout_drones_message = 'added ';
				}
				$scout_drones_message .= pluralise($change_scout_drones, 'scout drone');
				$msgParts[] = $scout_drones_message;
			} elseif ($change_scout_drones < 0) {
				$scout_drones_message = '';
				if ($change_combat_drones > 0 || ($change_combat_drones === 0 && $change_mines >= 0)) {
					$scout_drones_message = 'removed ';
				}
				$scout_drones_message .= pluralise(abs($change_scout_drones), 'scout drone');
				$msgParts[] = $scout_drones_message;
			}

			// now compile it together
			$message = $player->getBBLink() . ' has ' . format_list($msgParts);

			if ($change_mines >= 0 && $change_combat_drones >= 0 && $change_scout_drones >= 0) {
				$message .= ' to';
			} elseif ($change_mines <= 0 && $change_combat_drones <= 0 && $change_scout_drones <= 0) {
				$message .= ' from';
			} else {
				$message .= ' from/to';
			}

			$message .= ' your stack in sector ' . Globals::getSectorBBLink($forces->getSectorID());

			$player->sendMessage($forces->getOwnerID(), MSG_SCOUT, $message, false);
		}

		$player->log(LOG_TYPE_FORCES, $change_combat_drones . ' combat drones, ' . $change_scout_drones . ' scout drones, ' . $change_mines . ' mines');

		$forces->updateExpire();
		$forces->update(); // Needs to be in db to show up on CS instantly when querying sector forces

		// If we dropped forces from the Local Map, stay on that page
		if ($this->referrer === LocalMap::class) {
			$container = new LocalMap();
		} else {
			$container = new CurrentSector();
		}
		$container->go();
	}

}
