<?php declare(strict_types=1);

use Smr\Request;

$usedNames = [];

$i = 1;
$attackers = [];
if (Request::has('attackers')) {
	foreach (Request::getArray('attackers') as $attackerName) {
		if ($attackerName == 'none') {
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
	if ($defenderName == 'none') {
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
		if (count($attackersLeft) == 0 || count($defendersLeft) == 0) {
			break;
		}
		$results = runAnAttack($attackersLeft, $defendersLeft);
	}
}

/**
 * @param array<int, AbstractSmrPlayer> $realAttackers
 * @param array<int, AbstractSmrPlayer> $realDefenders
 * @return array<string, mixed>
 */
function runAnAttack(array $realAttackers, array $realDefenders): array {
	$results = [
		'Attackers' => ['Traders' => [], 'TotalDamage' => 0],
		'Defenders' => ['Traders' => [], 'TotalDamage' => 0],
	];
	foreach ($realAttackers as $accountID => $teamPlayer) {
		$playerResults = $teamPlayer->shootPlayers($realDefenders);
		$results['Attackers']['Traders'][] = $playerResults;
		$results['Attackers']['TotalDamage'] += $playerResults['TotalDamage'];
	}
	foreach ($realDefenders as $accountID => $teamPlayer) {
		$playerResults = $teamPlayer->shootPlayers($realAttackers);
		$results['Defenders']['Traders'][] = $playerResults;
		$results['Defenders']['TotalDamage'] += $playerResults['TotalDamage'];
	}
	return $results;
}

// Save ships unless we're just updating the dummy list
if (!Request::has('update')) {
	DummyShip::saveDummyShips();
}

$container = Page::create('admin/combat_simulator.php');
if (isset($results)) {
	$container['results'] = $results;
}
$container['attackers'] = $attackers;
$container['defenders'] = $defenders;
$container->go();
