<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$account = $session->getAccount();
$player = $session->getPlayer();
$sector = $player->getSector();

if ($player->hasNewbieTurns()) {
	create_error('You are under newbie protection.');
}
if ($player->hasFederalProtection()) {
	create_error('You are under federal protection.');
}
if ($player->isLandedOnPlanet()) {
	create_error('You cannot attack whilst on a planet!');
}
if ($player->getTurns() < TURNS_TO_SHOOT_SHIP) {
	create_error('You have insufficient turns to perform that action.');
}
if (!$player->canFight()) {
	create_error('You are not allowed to fight!');
}

$targetPlayer = SmrPlayer::getPlayer($var['target'], $player->getGameID());

if ($player->traderNAPAlliance($targetPlayer)) {
	create_error('Your alliance does not allow you to attack this trader.');
} elseif ($targetPlayer->isDead()) {
	create_error('Target is already dead.');
} elseif ($targetPlayer->getSectorID() != $player->getSectorID()) {
	create_error('Target is no longer in this sector.');
} elseif ($targetPlayer->hasNewbieTurns()) {
	create_error('Target is under newbie protection.');
} elseif ($targetPlayer->isLandedOnPlanet()) {
	create_error('Target is protected by planetary shields.');
} elseif ($targetPlayer->hasFederalProtection()) {
	create_error('Target is under federal protection.');
}

$fightingPlayers = $sector->getFightingTraders($player, $targetPlayer);

// Randomize players so that the attack order is always different
shuffle($fightingPlayers['Attackers']);
shuffle($fightingPlayers['Defenders']);

//decloak all fighters
foreach ($fightingPlayers as $teamPlayers) {
	foreach ($teamPlayers as $teamPlayer) {
		$teamPlayer->getShip()->decloak();
	}
}

// Take off the 3 turns for attacking
$player->takeTurns(TURNS_TO_SHOOT_SHIP);
$player->update();

function teamAttack(array $fightingPlayers, string $attack, string $defend): array {
	$results = ['Traders' => [], 'TotalDamage' => 0];
	foreach ($fightingPlayers[$attack] as $accountID => $teamPlayer) {
		$playerResults = $teamPlayer->shootPlayers($fightingPlayers[$defend]);
		$results['Traders'][$teamPlayer->getAccountID()] = $playerResults;
		$results['TotalDamage'] += $playerResults['TotalDamage'];

		// Award assists (if there are multiple attackers)
		if (count($fightingPlayers[$attack]) > 1) {
			foreach ($playerResults['Weapons'] as $weaponResults) {
				if (isset($weaponResults['KillResults'])) {
					foreach ($fightingPlayers[$attack] as $assistPlayer) {
						if (!$assistPlayer->equals($teamPlayer)) {
							$assistPlayer->increaseAssists(1);
						}
					}
				}
			}
		}
	}
	return $results;
}

$results = [
	'Attackers' => teamAttack($fightingPlayers, 'Attackers', 'Defenders'),
	'Defenders' => teamAttack($fightingPlayers, 'Defenders', 'Attackers'),
];

$account->log(LOG_TYPE_TRADER_COMBAT, 'Player attacks player, their team does ' . $results['Attackers']['TotalDamage'] . ' and the other team does ' . $results['Defenders']['TotalDamage'], $sector->getSectorID());

$db = Smr\Database::getInstance();
$db->insert('combat_logs', [
	'game_id' => $db->escapeNumber($player->getGameID()),
	'type' => $db->escapeString('PLAYER'),
	'sector_id' => $db->escapeNumber($sector->getSectorID()),
	'timestamp' => $db->escapeNumber(Smr\Epoch::time()),
	'attacker_id' => $db->escapeNumber($player->getAccountID()),
	'attacker_alliance_id' => $db->escapeNumber($player->getAllianceID()),
	'defender_id' => $db->escapeNumber($var['target']),
	'defender_alliance_id' => $db->escapeNumber($targetPlayer->getAllianceID()),
	'result' => $db->escapeObject($results, true),
]);

// If player died they are now in another sector, and thus locks need reset
if ($player->isDead()) {
	saveAllAndReleaseLock(updateSession: false);
	// Grab the lock in the new sector to avoid reloading session
	Smr\SectorLock::getInstance()->acquireForPlayer($player);
}

// If they died on the shot they get to see the results
$container = Page::create('skeleton.php', 'trader_attack.php', skipRedirect: $player->isDead());

// If player or target is dead there is no continue attack button
if ($player->isDead() || $targetPlayer->isDead()) {
	$container['target'] = 0;
} else {
	$container->addVar('target');
}

$container['results'] = $results;
$container->go();
