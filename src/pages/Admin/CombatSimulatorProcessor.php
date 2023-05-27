<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\DummyShip;
use Smr\Page\AccountPageProcessor;
use Smr\Request;

/**
 * @param array<int, \Smr\AbstractPlayer> $realAttackers
 * @param array<int, \Smr\AbstractPlayer> $realDefenders
 * @return array<string, mixed>
 */
function runAnAttack(array $realAttackers, array $realDefenders): array {
	$results = [
		'Attackers' => ['Traders' => [], 'TotalDamage' => 0],
		'Defenders' => ['Traders' => [], 'TotalDamage' => 0],
	];
	foreach ($realAttackers as $teamPlayer) {
		$playerResults = $teamPlayer->getShip()->shootPlayers($realDefenders);
		$results['Attackers']['Traders'][] = $playerResults;
		$results['Attackers']['TotalDamage'] += $playerResults['TotalDamage'];
	}
	foreach ($realDefenders as $teamPlayer) {
		$playerResults = $teamPlayer->getShip()->shootPlayers($realAttackers);
		$results['Defenders']['Traders'][] = $playerResults;
		$results['Defenders']['TotalDamage'] += $playerResults['TotalDamage'];
	}
	return $results;
}

class CombatSimulatorProcessor extends AccountPageProcessor {

	public function build(Account $account): never {
		$usedNames = [];

		$i = 1;
		$attackers = [];
		if (Request::has('attackers')) {
			foreach (Request::getArray('attackers') as $attackerName) {
				if ($attackerName === 'none') {
					continue;
				}
				if (isset($usedNames[$attackerName])) {
					create_error('Duplicate name used: ' . $attackerName);
				}
				$usedNames[$attackerName] = true;
				$attackers[$i] = DummyShip::getCachedDummyShip($attackerName)->getPlayer();
				++$i;
			}
		}

		$i = 1;
		$defenders = [];
		foreach (Request::getArray('defenders') as $defenderName) {
			if ($defenderName === 'none') {
				continue;
			}
			if (isset($usedNames[$defenderName])) {
				create_error('Duplicate name used: ' . $defenderName);
			}
			$usedNames[$defenderName] = true;
			$defenders[$i] = DummyShip::getCachedDummyShip($defenderName)->getPlayer();
			++$i;
		}

		if (Request::has('repair')) {
			foreach ([...$attackers, ...$defenders] as $player) {
				$player->setDead(false);
				$player->getShip()->setHardwareToMax();
			}
		}

		$results = null;
		if (Request::has('run') || Request::has('death_run')) {
			if (Request::has('death_run')) {
				$maxRounds = 100;
			} else {
				$maxRounds = 1;
			}
			$attackersLeft = $attackers;
			$defendersLeft = $defenders;
			for ($round = 0; $round < $maxRounds; $round++) {
				foreach ($attackersLeft as $key => $teamPlayer) {
					if ($teamPlayer->isDead()) {
						unset($attackersLeft[$key]);
					}
				}
				foreach ($defendersLeft as $key => $teamPlayer) {
					if ($teamPlayer->isDead()) {
						unset($defendersLeft[$key]);
					}
				}
				if (count($attackersLeft) === 0 || count($defendersLeft) === 0) {
					break;
				}
				$results = runAnAttack($attackersLeft, $defendersLeft);
			}
		}

		// Save ships unless we're just updating the dummy list
		if (!Request::has('update')) {
			DummyShip::saveDummyShips();
		}

		$container = new CombatSimulator($results, $attackers, $defenders);
		$container->go();
	}

}
