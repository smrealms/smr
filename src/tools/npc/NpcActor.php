<?php declare(strict_types=1);

namespace Smr\Npc;

use Exception;
use Smr\Database;
use Smr\Epoch;
use Smr\Force;
use Smr\Npc\Exceptions\AbandonTradeRoute;
use Smr\Npc\Exceptions\FinalAction;
use Smr\Npc\Exceptions\ForwardAction;
use Smr\Page\PlayerPageProcessor;
use Smr\Pages\Player\AllianceManageNpcsDismissProcessor;
use Smr\Pages\Player\AttackForcesProcessor;
use Smr\Pages\Player\Bank\AllianceBankProcessor;
use Smr\Player;
use Smr\Routes\RouteIterator;
use Smr\SectorLock;
use Smr\Session;
use Smr\TransactionType;

class NpcActor {

	/** @var array<\Smr\Routes\MultiplePortRoute> */
	private array $allTradeRoutes;
	private ?RouteIterator $tradeRoute = null;
	private int $actions = 0;
	private readonly int $startingCredits;
	private bool $hired;
	private readonly ?int $npcGalaxyID;
	private bool $isReturningToSafety = false;
	private ?PlayerPageProcessor $previousAction = null;

	public function __construct(
		private readonly int $playerID,
	) {
		$player = $this->refreshPlayer();

		// Sanity check to be certain we actually have an NPC
		if (!$player->isNPC()) {
			throw new Exception('Player is not an NPC!');
		}

		$player->updateTurns();
		checkStartConditions($player);

		debug('Player Status', [
			'Ship' => $player->getShip()->getName(),
			'Turns' => $player->getTurns(),
			'Credits' => $player->getCredits(),
			'Exp' => $player->getExperience(),
			'Alliance' => $player->getAlliance()->getAllianceName(),
		]);

		// Ensure the NPC doesn't think it's under attack at startup,
		// since this could cause it to get stuck in a loop in Fed.
		$player->setUnderAttack(false);

		// Forget any previously plotted course. There should be none,
		// but this ensures a clean startup even if shutdown was unclean.
		$player->deletePlottedCourse();

		// Get NPC Galaxy (from alliance leader's planet)
		$this->npcGalaxyID = self::getNpcGalaxyID($player);

		// Initialize the trade route for this NPC
		$this->allTradeRoutes = findRoutes($player, $this->npcGalaxyID);
		shuffle($this->allTradeRoutes); // randomize
		$this->changeRoute();

		// Upgrade ship if possible, reset hardware to max, etc.
		setupShip($player);

		// Launch from planet, if necessary
		$player->setLandedOnPlanet(false);

		// Update database (not essential to have a lock here)
		$player->update();

		$this->startingCredits = $player->getCredits();
		$this->hired = $player->isHiredNPC();
	}

	private static function getNpcGalaxyID(Player $player): ?int {
		if ($player->hasAlliance()) {
			$alliance = $player->getAlliance();
			if ($alliance->hasLeader()) {
				$leader = $alliance->getLeader();
				// This *must* be null for NPCs in normal player alliances!
				if ($leader->isNPC()) {
					return $leader->getPlanet()?->getGalaxy()->getGalaxyID();
				}
			}
		}
		return null;
	}

	private function refreshPlayer(): Player {
		return Player::getPlayer($this->playerID, forceUpdate: true);
	}

	public function hasTakenActions(): bool {
		return $this->actions > 0;
	}

	public function shutdown(): void {
		$player = $this->refreshPlayer();
		$sector = $player->getSector();
		if ($sector->offersFederalProtection() && !$player->hasFederalProtection()) {
			debug('Disarming so we can get Fed protection');
			$player->getShip()->setCDs(0);
			$player->getShip()->removeAllWeapons();
			$player->getShip()->update();
		} elseif ($sector->hasPlanet()) {
			$planet = $sector->getPlanet();
			if (!$planet->hasOwner() || $planet->getOwner()->sameAlliance($player)) {
				debug('Landing on planet');
				$player->setNewbieTurns(0);
				$player->setLandedOnPlanet(true);
				$player->update();
			}
		}

		if ($this->hired) {
			// Give half earned profits to employer
			$credits = IFloor(max(0, $player->getCredits() - $this->startingCredits) / 2);
			debug('Giving ' . $credits . ' credits to alliance');
			AllianceBankProcessor::doTransaction(
				action: 'Deposit',
				alliance: $player->getAlliance(),
				player: $player,
				message: 'Profits from trading',
				amount: $credits,
			);
			$player->update();
		}
	}

	public function processNextAction(): never {
		// We have to reload player on each loop
		$player = $this->refreshPlayer();
		$action = $this->getNextAction($player);

		global $forwardedContainer;
		if ($this->previousAction !== null
			&& objects_equal($action, $this->previousAction)
			&& !($forwardedContainer instanceof AttackForcesProcessor)) {
			debug('We are executing the same action twice?', [
				'ForwardedContainer' => $forwardedContainer,
				'Action' => $action,
			]);
			if (!$player->canFight()) {
				// Only throw the exception if we have protection, otherwise let's hope
				// that the NPC will find its way to safety rather than dying in the open.
				throw new Exception('We are executing the same action twice?');
			}
		}
		$this->previousAction = $action;
		debug('Executing action', $action);
		// The next "page request" must occur at an updated time.
		Epoch::update();
		Session::getInstance()->setCurrentVar($action);

		// Acquire a lock in the sector where we chose our action
		$lock = SectorLock::getInstance();
		$lock->acquireForPlayer($player);

		clearCaches(); // do not retain anything from before lock acquisition
		$player = $this->refreshPlayer();
		if ($player->getSectorID() !== $lock->getSectorID()) {
			// NPC sector was modified externally (e.g. back to HQ in a pod) while
			// deciding what to do, so skip this action and select a new action.
			debug('Player sector has changed from lock sector. Will try another action.');
			throw new ForwardAction();
		}

		$action->build($player);
	}

	private function getNextAction(Player $player): PlayerPageProcessor {
		// Avoid infinite loops by restricting the number of actions
		if ($this->actions >= NPC_MAX_ACTIONS) {
			debug('Reached maximum number of actions: ' . NPC_MAX_ACTIONS);
			throw new FinalAction();
		}

		$player->updateTurns();

		if ($player->isDead()) {
			debug('Some evil person killed us, let\'s move on now.');

			$player->setDead(false); // see death_processing.php
			$player->setNewbieWarning(false); // undo Player::killPlayer setting this to true

			// If NPC was locked to ship, unlock it so they can upgrade from pod.
			$db = Database::getInstance();
			$db->update(
				'npc_players',
				['lock_ship' => $db->escapeBoolean(false)],
				$player->SQLID,
			);

			// Hired traders quit their job after getting podded while acting.
			// If no action yet, don't dismiss since this might be a new employer.
			if ($this->hired && $this->hasTakenActions()) {
				debug('Leaving alliance: ' . $player->getAlliance()->getAllianceName());
				AllianceManageNpcsDismissProcessor::dismissNpc($player, $player);
				$this->hired = false;
				throw new FinalAction();
			}

			checkStartConditions($player);

			// We died, we don't care what we were doing beforehand.
			$this->previousAction = null;
			setupShip($player); // reship before continuing
			$this->changeRoute(avoidPreviousPorts: true);
		}

		// Start the action sequence
		$this->actions++;
		debug('Action #' . $this->actions);

		if ($this->isReturningToSafety) {
			// Returning to safety is our highest priority action
			if ($player->hasPlottedCourse()) {
				return $this->moveToNextSector($player);
			}
			// We've reached the end of our plotted course to safety
			throw new FinalAction();
		}
		if ($player->isUnderAttack()) {
			// We're under attack and need to plot course to safety.
			debug('Under Attack');
			return $this->returnToSafety($player);
		}
		if ($player->getTurns() < NPC_LOW_TURNS) {
			// We're low on turns or have been under attack and need to plot course to fed
			if (!$player->canFight()) {
				debug('We are protected, time to switch to another NPC.');
				throw new FinalAction();
			}
			debug('Low Turns:' . $player->getTurns());
			return $this->returnToSafety($player);
		}
		if ($player->hasPlottedCourse()) {
			// We have a route to follow
			return $this->moveToNextSector($player);
		}
		if ($this->tradeRoute instanceof RouteIterator) {
			debug('Trade Route');

			$currentRoute = $this->tradeRoute->getCurrentRoute();
			$transaction = $this->tradeRoute->getCurrentTransaction();
			$targetSectorID = $this->tradeRoute->getCurrentSectorID();

			if ($targetSectorID !== $player->getSectorID()) {
				// We're not at the right port yet, let's plot to it.
				debug('Plot To ' . $transaction->value . ': ' . $targetSectorID);
				return plotToSector($player, $targetSectorID);
			}

			$port = $player->getSector()->getPort();
			$tradeRestriction = $port->getTradeRestriction($player);
			if ($tradeRestriction !== false) {
				debug('We cannot trade at this port: ' . $tradeRestriction);
				$this->changeRoute();
				throw new ForwardAction();
			}

			if ($transaction === TransactionType::Buy && $player->getShip()->hasCargo()) {
				// We're here to buy, but we have cargo already
				debug('Dump Goods');
				return dumpCargo($player);
			}

			// Advance the route iterator for next action
			$this->tradeRoute->next();
			try {
				return tradeGoods($currentRoute->getGoodID(), $player, $port);
			} catch (AbandonTradeRoute $err) {
				debug('Abandoning current trade route: ' . $err->getMessage());
				$this->changeRoute();
				throw new ForwardAction();
			}
		}
		debug('No valid actions to take');
		return $this->returnToSafety($player);
		/*
		//Otherwise let's run around at random.
		$moveTo = array_rand_value($player->getSector()->getLinks());
		debug('Random Wanderings: ' . $moveTo);
		return moveToSector($player, $moveTo);
		*/
	}

	private function moveToNextSector(Player $player): PlayerPageProcessor {
		// Before we move, lay forces if we're in our NPC galaxy
		if ($this->npcGalaxyID !== null) {
			$sector = $player->getSector();
			if ($this->npcGalaxyID === $sector->getGalaxyID() && !$sector->hasWarp()) {
				$force = Force::getForce($player->getGameID(), $sector->getSectorID(), $player->getPlayerID());
				$force->setForcesToMax();
				$force->setExpire($player->getGame()->getEndTime());
				$force->update();
			}
		}
		if (!$player->hasPlottedCourse()) {
			throw new Exception('This should only be called when we have a plotted course');
		}
		debug('Follow Course: ' . $player->getPlottedCourse()->getNextOnPath());
		return moveToSector($player, $player->getPlottedCourse()->getNextOnPath());
	}

	private function returnToSafety(Player $player): PlayerPageProcessor {
		$this->isReturningToSafety = true;
		return plotToSafety($player);
	}

	private function changeRoute(bool $avoidPreviousPorts = false): void {
		// Remove any route from the pool of available routes if it contains any
		// the sectors in the current route (e.g. we died on it, don't return).
		if ($avoidPreviousPorts && $this->tradeRoute !== null) {
			$avoidSectorIDs = $this->tradeRoute->getEntireRoute()->getPortSectorIDs();
			foreach ($this->allTradeRoutes as $key => $route) {
				foreach ($avoidSectorIDs as $avoidSectorID) {
					if ($route->containsPort($avoidSectorID)) {
						unset($this->allTradeRoutes[$key]);
						break;
					}
				}
			}
		}

		if (count($this->allTradeRoutes) === 0) {
			$this->tradeRoute = null;
			return;
		}

		// Remove the picked route we chose so that we don't pick it again later.
		$route = array_pop($this->allTradeRoutes);

		debug('Switched route', $route);
		$this->tradeRoute = new RouteIterator($route);
	}

}
