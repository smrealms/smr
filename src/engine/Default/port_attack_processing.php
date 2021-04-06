<?php declare(strict_types=1);

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


if ($port->isDestroyed()) {
	Page::create('skeleton.php', 'port_attack.php')->go();
}


// ********************************
// *
// * P o r t   a t t a c k
// *
// ********************************

$results = array('Attackers' => array('TotalDamage' => 0),
				'Forces' => array(),
				'Forced' => false);

$attackers = $sector->getFightingTradersAgainstPort($player, $port);

$port->attackedBy($player, $attackers);

// take the turns and decloak all attackers
foreach ($attackers as $attacker) {
	$attacker->takeTurns(TURNS_TO_SHOOT_PORT, 0);
	$attacker->getShip()->decloak();
}

$totalShieldDamage = 0;
foreach ($attackers as $attacker) {
	$playerResults = $attacker->shootPort($port);
	$results['Attackers']['Traders'][$attacker->getAccountID()] = $playerResults;
	$results['Attackers']['TotalDamage'] += $playerResults['TotalDamage'];
	foreach ($playerResults['Weapons'] as $weapon) {
		if (isset($weapon['ActualDamage'])) { // Only set if the weapon hits
			$totalShieldDamage += $weapon['ActualDamage']['Shield'];
		}
	}
}

// Planet downgrades only occur on non-shield damage
$downgradeDamage = $results['Attackers']['TotalDamage'] - $totalShieldDamage;
$results['Attackers']['Downgrades'] = $port->checkForDowngrade($downgradeDamage);

$results['Port'] = $port->shootPlayers($attackers);

$account->log(LOG_TYPE_PORT_RAIDING, 'Player attacks port, the port does ' . $results['Port']['TotalDamage'] . ', their team does ' . $results['Attackers']['TotalDamage'] . ' and downgrades ' . $results['Attackers']['Downgrades'] . ' levels.', $port->getSectorID());

$port->update();

$db->query('INSERT INTO combat_logs VALUES(\'\',' . $db->escapeNumber($player->getGameID()) . ',\'PORT\',' . $db->escapeNumber($port->getSectorID()) . ',' . $db->escapeNumber(Smr\Epoch::time()) . ',' . $db->escapeNumber($player->getAccountID()) . ',' . $db->escapeNumber($player->getAllianceID()) . ',' . $db->escapeNumber(ACCOUNT_ID_PORT) . ',' . $db->escapeNumber(PORT_ALLIANCE_ID) . ',' . $db->escapeObject($results, true) . ')');
$logId = $db->escapeString('[ATTACK_RESULTS]' . $db->getInsertID());
foreach ($attackers as $attacker) {
	if (!$player->equals($attacker)) {
		$db->query('REPLACE INTO sector_message VALUES(' . $db->escapeNumber($attacker->getAccountID()) . ',' . $db->escapeNumber($attacker->getGameID()) . ',' . $logId . ')');
	}
}

$container = Page::create('skeleton.php', 'port_attack.php');

// If they died on the shot they get to see the results
if ($player->isDead()) {
	$container['override_death'] = TRUE;
}

$container['results'] = $results;
$container->go();
