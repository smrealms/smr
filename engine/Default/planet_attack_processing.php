<?php declare(strict_types=1);

if ($player->hasNewbieTurns())
	create_error('You are under newbie protection!');
if ($player->hasFederalProtection())
	create_error('You are under federal protection!');
if ($player->isLandedOnPlanet())
	create_error('You cannot attack planets whilst on a planet!');
if ($player->getTurns() < 3)
	create_error('You do not have enough turns to attack this planet!');
if (!$ship->hasWeapons() && !$ship->hasCDs())
	create_error('What are you going to do? Insult it to death?');
if (!$player->canFight())
	create_error('You are not allowed to fight!');

$planet = $player->getSectorPlanet();
if (!$planet->exists())
	create_error('This planet does not exist.');
if (!$planet->isClaimed())
	create_error('This planet is not claimed.');

$planetOwner = $planet->getOwner();

if ($player->forceNAPAlliance($planetOwner)) {
	create_error('You have a planet NAP, you cannot attack this planet!');
}

// take the turns
$player->takeTurns(3, 0);


// ********************************
// *
// * P l a n e t   a t t a c k
// *
// ********************************

$results = array('Attackers' => array('TotalDamage' => 0),
				'Forces' => array(),
				'Forced' => false);

$attackers = $sector->getFightingTradersAgainstPlanet($player, $planet);

$planet->attackedBy($player, $attackers);

//decloak all attackers
foreach ($attackers as $attacker) {
	$attacker->getShip()->decloak();
}

foreach ($attackers as $attacker) {
	$playerResults =& $attacker->shootPlanet($planet, false);
	$results['Attackers']['Traders'][$attacker->getAccountID()] =& $playerResults;
	$results['Attackers']['TotalDamage'] += $playerResults['TotalDamage'];
}
$results['Attackers']['Downgrades'] = $planet->checkForDowngrade($results['Attackers']['TotalDamage']);
$results['Planet'] =& $planet->shootPlayers($attackers);

$account->log(LOG_TYPE_PLANET_BUSTING, 'Player attacks planet, the planet does ' . $results['Planet']['TotalDamage'] . ', their team does ' . $results['Attackers']['TotalDamage'] . ' and downgrades: ' . var_export($results['Attackers']['Downgrades'], true), $planet->getSectorID());

$ship->removeUnderAttack(); //Don't show attacker the under attack message.

// Add this log to the `combat_logs` database table
$serializedResults = serialize($results);
$db->query('INSERT INTO combat_logs VALUES(\'\',' . $db->escapeNumber($player->getGameID()) . ',\'PLANET\',' . $planet->getSectorID() . ',' . TIME . ',' . $db->escapeNumber($player->getAccountID()) . ',' . $db->escapeNumber($player->getAllianceID()) . ',' . $planetOwner->getAccountID() . ',' . $planetOwner->getAllianceID() . ',' . $db->escapeBinary(gzcompress($serializedResults)) . ')');
unserialize($serializedResults); //because of references we have to undo this.
$logId = $db->getInsertID();

if ($planet->isDestroyed()) {
	$db->query('UPDATE player SET land_on_planet = \'FALSE\' WHERE sector_id = ' . $db->escapeNumber($planet->getSectorID()) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()));
	$planet->removeClaimed();
	$planet->removePassword();

	// Prepare message for planet owners
	$planetAttackMessage = 'The defenses of ' . $planet->getDisplayName() . ' have been breached. The planet is lost! [combatlog=' . $logId . ']';
} else {
	$planetAttackMessage = 'Reports from the surface of ' . $planet->getDisplayName() . ' confirm that it is under <span class="red">attack</span>! [combatlog=' . $logId . ']';
}

// Send notification to planet owners
if ($planetOwner->hasAlliance()) {
	foreach ($planetOwner->getAlliance()->getMemberIDs() as $allyAccountID) {
		SmrPlayer::sendMessageFromPlanet($planet->getGameID(), $allyAccountID, $planetAttackMessage);
	}
} else {
	SmrPlayer::sendMessageFromPlanet($planet->getGameID(), $planetOwner->getAccountID(), $planetAttackMessage);
}

// Update sector messages for attackers
foreach ($attackers as $attacker) {
	if (!$player->equals($attacker))
		$db->query('REPLACE INTO sector_message VALUES(' . $attacker->getAccountID() . ',' . $attacker->getGameID() . ',' . $db->escapeString('[ATTACK_RESULTS]' . $logId) . ')');
}

$container = create_container('skeleton.php', 'planet_attack.php');
$container['sector_id'] = $planet->getSectorID();

// If they died on the shot they get to see the results
if ($player->isDead()) {
	$container['override_death'] = TRUE;
}

$container['results'] = $serializedResults;
forward($container);
