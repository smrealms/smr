<?php declare(strict_types=1);

$template = Smr\Template::getInstance();

$template->assign('PageTopic', 'Combat Simulator');

$template->assign('EditDummysLink', Page::create('skeleton.php', 'admin/edit_dummys.php')->href());
$template->assign('DummyNames', DummyPlayer::getDummyPlayerNames());

$duplicates = false;
$usedNames = [];
$realAttackers = [];
$attackers = [];
$i = 1;
if (Smr\Request::has('attackers')) {
	foreach (Smr\Request::getArray('attackers') as $attackerName) {
		if ($attackerName == 'none') {
			continue;
		}
		if (isset($usedNames[$attackerName])) {
			$duplicates = true;
			continue;
		}
		$usedNames[$attackerName] = true;
		$attackers[$i] = DummyPlayer::getCachedDummyPlayer($attackerName);
		$realAttackers[$i] = $attackers[$i];
		++$i;
	}
}

for (;$i <= 10; ++$i) {
	$attackers[$i] = null;
}
$template->assign('Attackers', $attackers);

$i = 1;
$realDefenders = [];
$defenders = [];
if (Smr\Request::has('defenders')) {
	foreach (Smr\Request::getArray('defenders') as $defenderName) {
		if ($defenderName == 'none') {
			continue;
		}
		if (isset($usedNames[$defenderName])) {
			$duplicates = true;
			continue;
		}
		$usedNames[$defenderName] = true;
		$defenders[$i] = DummyPlayer::getCachedDummyPlayer($defenderName);
		$realDefenders[$i] = $defenders[$i];
		++$i;
	}
}

for (;$i <= 10; ++$i) {
	$defenders[$i] = null;
}
$template->assign('Defenders', $defenders);

$template->assign('Duplicates', $duplicates);

$template->assign('CombatSimHREF', Page::create('skeleton.php', 'admin/combat_simulator.php')->href());

if (!empty($realAttackers) && !empty($realDefenders)) {
	if (Smr\Request::has('run')) {
		$results = runAnAttack($realAttackers, $realDefenders);
		$template->assign('TraderCombatResults', $results);
	}
	if (Smr\Request::has('death_run')) {
		while (count($realAttackers) > 0 && count($realDefenders) > 0) {
			$results = runAnAttack($realAttackers, $realDefenders);
			foreach ($realAttackers as $key => $teamPlayer) {
				if ($teamPlayer->isDead()) {
					unset($realAttackers[$key]);
				}
			}
			foreach ($realDefenders as $key => $teamPlayer) {
				if ($teamPlayer->isDead()) {
					unset($realDefenders[$key]);
				}
			}
		}
		$template->assign('TraderCombatResults', $results);
	}
}

function runAnAttack(array $realAttackers, array $realDefenders) : array {
	$results = ['Attackers' => ['Traders' => [], 'TotalDamage' => 0],
					'Defenders' => ['Traders' => [], 'TotalDamage' => 0]];
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
