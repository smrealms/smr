<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Combat\Results\Combatant\PortAttackerCombatResults;
use Smr\Combat\Results\Full\PortFullCombatResults;
use Smr\Database;
use Smr\Epoch;
use Smr\Page\PlayerPageProcessor;
use Smr\Player;
use Smr\SectorLock;

class AttackPortProcessor extends PlayerPageProcessor {

	public function build(Player $player): never {
		$account = $player->getAccount();
		$ship = $player->getShip();
		$sector = $player->getSector();

		if ($player->hasNewbieTurns()) {
			create_error('You are under newbie protection!');
		}
		if ($player->hasFederalProtection()) {
			create_error('You are under federal protection!');
		}
		if ($player->isLandedOnPlanet()) {
			create_error('You cannot attack ports whilst on a planet!');
		}
		if ($player->getTurns() < TURNS_TO_SHOOT_PORT) {
			create_error('You do not have enough turns to attack this port!');
		}
		if (!$ship->hasWeapons() && !$ship->hasCDs()) {
			create_error('What are you going to do? Insult it to death?');
		}
		if (!$player->canFight()) {
			create_error('You are not allowed to fight!');
		}

		$port = $sector->getPort();

		if (!$port->exists()) {
			create_error('This port does not exist.');
		}

		if ($port->isBusted()) {
			new AttackPort()->go();
		}

		$attackers = $sector->getFightingTradersAgainstPort($player, $port);
		if (count($attackers) === 0) {
			create_error('No players in sector are able to attack this port!');
		}

		// ********************************
		// *
		// * P o r t   a t t a c k
		// *
		// ********************************

		$attackerResults = [];
		$totalDamage = 0;

		$port->attackedBy($player, $attackers);

		// take the turns and decloak all attackers
		foreach ($attackers as $attacker) {
			$attacker->takeTurns(TURNS_TO_SHOOT_PORT);
			$attacker->getShip()->decloak();
		}

		$totalShieldDamage = 0;
		foreach ($attackers as $attacker) {
			$playerResults = $attacker->getShip()->shootPort($port);
			$attackerResults[$attacker->getAccountID()] = $playerResults;
			$totalDamage += $playerResults->getTotalDamage();
			foreach ($playerResults->weaponResults as $weapon) {
				if (isset($weapon->actualDamage)) { // Only set if the weapon hits
					$totalShieldDamage += $weapon->actualDamage->shieldDamage;
				}
			}
		}

		// Port downgrades only occur on non-shield damage
		$downgradeDamage = $totalDamage - $totalShieldDamage;
		$attackerResults = new PortAttackerCombatResults($totalDamage, $attackerResults, $port->checkForDowngrade($downgradeDamage));

		$results = new PortFullCombatResults(
			attackers: $attackerResults,
			port: $port->shootPlayers($attackers),
		);

		$account->log(LOG_TYPE_PORT_RAIDING, 'Player attacks port, the port does ' . $results->port->getTotalDamage() . ', their team does ' . $results->attackers->totalDamage . ' and downgrades ' . $results->attackers->downgrades . ' levels.', $port->getSectorID());

		$port->update();

		$db = Database::getInstance();
		$logId = $db->insertAutoIncrement('combat_logs', [
			'game_id' => $player->getGameID(),
			'type' => 'PORT',
			'sector_id' => $port->getSectorID(),
			'timestamp' => Epoch::time(),
			'attacker_id' => $player->getAccountID(),
			'attacker_alliance_id' => $player->getAllianceID(),
			'defender_id' => ACCOUNT_ID_PORT,
			'defender_alliance_id' => PORT_ALLIANCE_ID,
			'result' => $db->escapeObject($results, true),
		]);

		$sectorMessage = '[ATTACK_RESULTS]' . $logId;
		foreach ($attackers as $attacker) {
			if (!$player->equals($attacker)) {
				$db->replace('sector_message', [
					'account_id' => $attacker->getAccountID(),
					'game_id' => $attacker->getGameID(),
					'message' => $sectorMessage,
				]);
			}
		}

		// If player died they are now in another sector, and thus locks need reset
		if ($player->isDead()) {
			saveAllAndReleaseLock(updateSession: false);
			// Grab the lock in the new sector to avoid reloading session
			SectorLock::getInstance()->acquireForPlayer($player);
		}

		// If they died on the shot they get to see the results
		$container = new AttackPort($results, $player->isDead());
		$container->go();
	}

}
