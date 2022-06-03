<?php declare(strict_types=1);

namespace Smr\Npc;

use Exception;
use Page;
use Smr\Npc\Exceptions\FinalAction;
use Smr\Npc\Exceptions\ForwardAction;
use Smr\Npc\Exceptions\TradeRouteDrained;
use Smr\Routes\RouteIterator;
use SmrPlayer;
use SmrSector;

class NpcActor {

	/** @var array<MultiplePortRoute> */
	private array $allTradeRoutes;
	private ?RouteIterator $tradeRoute = null;
	private int $actions = 0;

	public function __construct(
		private readonly int $gameID,
		private readonly int $accountID,
	) {
		$player = $this->refreshPlayer();

		// Sanity check to be certain we actually have an NPC
		if (!$player->isNPC()) {
			throw new Exception('Player is not an NPC!');
		}

		checkStartConditions($player);

		// Ensure the NPC doesn't think it's under attack at startup,
		// since this could cause it to get stuck in a loop in Fed.
		$player->setUnderAttack(false);

		// Initialize the trade route for this NPC
		$this->allTradeRoutes = findRoutes($player);
		shuffle($this->allTradeRoutes); // randomize
		$this->changeRoute();

		// Upgrade ship if possible, reset hardware to max, etc.
		setupShip($player);

		// Update database (not essential to have a lock here)
		$player->update();
	}

	private function refreshPlayer(): SmrPlayer {
		return SmrPlayer::getPlayer($this->accountID, $this->gameID, true);
	}

	public function getNumActions(): int {
		return $this->actions;
	}

	public function shutdown(): void {
		$player = $this->refreshPlayer();
		if ($player->getSector()->offersFederalProtection() && !$player->hasFederalProtection()) {
			debug('Disarming so we can get Fed protection');
			$player->getShip()->setCDs(0);
			$player->getShip()->removeAllWeapons();
			$player->getShip()->update();
		}
	}

	public function getNextAction(): Page {

		// Avoid infinite loops by restricting the number of actions
		if ($this->actions >= NPC_MAX_ACTIONS) {
			debug('Reached maximum number of actions: ' . NPC_MAX_ACTIONS);
			throw new FinalAction();
		}

		// Start the action sequence
		$this->actions++;
		debug('Action #' . $this->actions);

		//We have to reload player on each loop
		$player = $this->refreshPlayer();
		$player->updateTurns();

		if ($player->isDead()) {
			debug('Some evil person killed us, let\'s move on now.');
			$player->setDead(false); // see death_processing.php
			$player->setNewbieWarning(false); // undo SmrPlayer::killPlayer setting this to true
			checkStartConditions($player);

			global $previousContainer;
			$previousContainer = null; //We died, we don't care what we were doing beforehand.
			setupShip($player); // reship before continuing
			$this->changeRoute();
		}

		// Do we have a plot that ends in Fed?
		$hasPlotToFed = $player->hasPlottedCourse() && SmrSector::getSector($player->getGameID(), $player->getPlottedCourse()->getEndSectorID())->offersFederalProtection();

		if ($hasPlotToFed) {
			// We have a route to fed to follow
			debug('Follow Course: ' . $player->getPlottedCourse()->getNextOnPath());
			return moveToSector($player, $player->getPlottedCourse()->getNextOnPath());
		}
		if ($player->isUnderAttack()) {
			// We're under attack and need to plot course to fed.
			debug('Under Attack');
			return plotToFed($player);
		}
		if ($player->getTurns() < NPC_LOW_TURNS) {
			// We're low on turns or have been under attack and need to plot course to fed
			if ($player->hasFederalProtection()) {
				debug('We are in fed, time to switch to another NPC.');
				throw new FinalAction();
			}
			if ($player->getTurns() < NPC_LOW_TURNS) {
				debug('Low Turns:' . $player->getTurns());
			}
			return plotToFed($player);
		}
		if ($player->hasPlottedCourse()) {
			// We have a route to follow
			debug('Follow Course: ' . $player->getPlottedCourse()->getNextOnPath());
			return moveToSector($player, $player->getPlottedCourse()->getNextOnPath());
		}
		if ($this->tradeRoute instanceof RouteIterator) {
			debug('Trade Route');

			$currentRoute = $this->tradeRoute->getCurrentRoute();
			$transaction = $this->tradeRoute->getCurrentTransaction();
			$targetSectorID = $this->tradeRoute->getCurrentSectorID();

			if ($targetSectorID != $player->getSectorID()) {
				// We're not at the right port yet, let's plot to it.
				debug('Plot To ' . $transaction . ': ' . $targetSectorID);
				return plotToSector($player, $targetSectorID);
			}

			$port = $player->getSector()->getPort();
			$tradeRestriction = $port->getTradeRestriction($player);
			if ($tradeRestriction !== false) {
				debug('We cannot trade at this port: ' . $tradeRestriction);
				$this->changeRoute();
				throw new ForwardAction();
			}

			if ($transaction == TRADER_BUYS && $player->getShip()->hasCargo()) {
				// We're here to buy, but we have cargo already
				debug('Dump Goods');
				return dumpCargo($player);
			}

			// Advance the route iterator for next action
			$this->tradeRoute->next();
			try {
				return tradeGoods($currentRoute->getGoodID(), $player, $port);
			} catch (TradeRouteDrained) {
				debug('Trade route is drained');
				$this->changeRoute();
				throw new ForwardAction();
			}
		}
		debug('No valid actions to take');
		return plotToFed($player);
		/*
		//Otherwise let's run around at random.
		$moveTo = array_rand_value($player->getSector()->getLinks());
		debug('Random Wanderings: ' . $moveTo);
		return moveToSector($player, $moveTo);
		*/
	}

	private function changeRoute(): void {
		// Remove any route from the pool of available routes if it contains any
		// the sectors in the current route (e.g. we died on it, don't return).
		if ($this->tradeRoute !== null) {
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

		if (count($this->allTradeRoutes) == 0) {
			$this->tradeRoute = null;
			return;
		}

		// Remove the picked route we chose so that we don't pick it again later.
		$route = array_pop($this->allTradeRoutes);

		debug('Switched route', $route);
		$this->tradeRoute = new RouteIterator($route);
	}

}
