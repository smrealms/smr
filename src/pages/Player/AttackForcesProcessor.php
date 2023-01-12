<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Database;
use Smr\Epoch;
use Smr\Force;
use Smr\Globals;
use Smr\Page\PlayerPageProcessor;
use Smr\SectorLock;

class AttackForcesProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly int $ownerAccountID,
		private readonly bool $bump = false
	) {}

	public function build(AbstractPlayer $player): never {
		$ship = $player->getShip();

		$forces = Force::getForce($player->getGameID(), $player->getSectorID(), $this->ownerAccountID);
		$forceOwner = $forces->getOwner();

		if ($player->hasNewbieTurns()) {
			create_error('You are under newbie protection!');
		}
		if ($player->hasFederalProtection()) {
			create_error('You are under federal protection.');
		}
		if ($player->isLandedOnPlanet()) {
			create_error('You cannot attack forces whilst on a planet!');
		}
		if (!$player->canFight()) {
			create_error('You are not allowed to fight!');
		}
		if ($player->forceNAPAlliance($forceOwner)) {
			create_error('You cannot attack allied forces!');
		}

		// The attack is processed slightly differently if the attacker bumped into mines
		// when moving into sector
		$bump = $this->bump;

		if ($bump) {
			if (!$forces->hasMines()) {
				create_error('No mines in sector!');
			}
		} else {
			if (!$forces->exists()) {
				create_error('These forces no longer exist.');
			}
			if ($player->getTurns() < $forces->getAttackTurnCost($ship)) {
				create_error('You do not have enough turns to attack these forces!');
			}
			if (!$ship->hasWeapons() && !$ship->hasCDs()) {
				create_error('You cannot attack without weapons!');
			}
		}

		// take the turns
		if ($bump) {
			$player->takeTurns($forces->getBumpTurnCost($ship));
		} else {
			$player->takeTurns($forces->getAttackTurnCost($ship), 1);
		}

		// delete plotted course
		$player->deletePlottedCourse();

		// A message will be sent if scouts are present before the attack.
		// Sending occurs after the attack so we can link the combat log.
		$sendMessage = $forces->hasSDs();

		// ********************************
		// *
		// * F o r c e s   a t t a c k
		// *
		// ********************************

		$results = ['Attackers' => ['TotalDamage' => 0],
						'Forces' => [],
						'Forced' => $bump];

		$attackers = $player->getSector()->getFightingTradersAgainstForces($player, $bump);

		//decloak all attackers
		foreach ($attackers as $attacker) {
			$attacker->getShip()->decloak();
			if (!$bump) {
				$attacker->setLastSectorID(0);
			}
		}

		// If mines are bumped, the forces shoot first. Otherwise player shoots first.
		if ($bump) {
			$results['Forces'] = $forces->shootPlayers($attackers, $bump);
		}

		$results['Attackers'] = ['TotalDamage' => 0];
		foreach ($attackers as $attacker) {
			$playerResults = $attacker->shootForces($forces);
			$results['Attackers']['Traders'][$attacker->getAccountID()] = $playerResults;
			$results['Attackers']['TotalDamage'] += $playerResults['TotalDamage'];
		}

		if (!$bump) {
			$results['Forces'] = $forces->shootPlayers($attackers, $bump);
			$forces->updateExpire();
		}

		// Add this log to the `combat_logs` database table
		$db = Database::getInstance();
		$logId = $db->insert('combat_logs', [
			'game_id' => $db->escapeNumber($player->getGameID()),
			'type' => $db->escapeString('FORCE'),
			'sector_id' => $db->escapeNumber($forces->getSectorID()),
			'timestamp' => $db->escapeNumber(Epoch::time()),
			'attacker_id' => $db->escapeNumber($player->getAccountID()),
			'attacker_alliance_id' => $db->escapeNumber($player->getAllianceID()),
			'defender_id' => $db->escapeNumber($forceOwner->getAccountID()),
			'defender_alliance_id' => $db->escapeNumber($forceOwner->getAllianceID()),
			'result' => $db->escapeObject($results, true),
		]);

		if ($sendMessage) {
			$message = 'Your forces in sector ' . Globals::getSectorBBLink($forces->getSectorID()) . ' are under <span class="red">attack</span> by ' . $player->getBBLink() . '! [combatlog=' . $logId . ']';
			$forces->ping($message, $player, true);
		}

		// If player died they are now in another sector, and thus locks need reset
		if ($player->isDead()) {
			saveAllAndReleaseLock(updateSession: false);
			// Grab the lock in the new sector to avoid reloading session
			SectorLock::getInstance()->acquireForPlayer($player);
		}

		// If player or target is dead there is no continue attack button
		if ($player->isDead() || !$forces->exists()) {
			$displayOwnerID = 0;
		} else {
			$displayOwnerID = $forces->getOwnerID();
		}

		$container = new AttackForces($displayOwnerID, $results, $player->isDead());
		$container->go();
	}

}
