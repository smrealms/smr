<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\DummyShip;
use Smr\Html\Submit;
use Smr\Page\AccountPageProcessor;
use Smr\Request;

/**
 * @param array<int, \Smr\Player> $realAttackers
 * @param array<int, \Smr\Player> $realDefenders
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

	private const string ACTION = 'action';

	public readonly Submit $actionUpdate;
	public readonly Submit $actionRepair;
	public readonly Submit $actionRun;
	public readonly Submit $actionRunAll;

	public function __construct() {
		$this->actionUpdate = new Submit(self::ACTION, 'update');
		$this->actionRepair = new Submit(self::ACTION, 'repair');
		$this->actionRun = new Submit(self::ACTION, 'run');
		$this->actionRunAll = new Submit(self::ACTION, 'run_all');
	}

	public function build(Account $account): never {
		$usedNames = [];

		$i = 1;
		$attackers = [];
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

		$action = Request::get(self::ACTION);
		if ($action === $this->actionRepair->value) {
			foreach ([...$attackers, ...$defenders] as $player) {
				$player->setDead(false);
				$player->getShip()->setHardwareToMax();
			}
		}

		$results = null;
		if ($action === $this->actionRun->value || $action === $this->actionRunAll->value) {
			if ($action === $this->actionRunAll->value) {
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
		if ($action !== $this->actionUpdate->value) {
			DummyShip::saveDummyShips();
		}

		$container = new CombatSimulator($results, $attackers, $defenders);
		$container->go();
	}

}
