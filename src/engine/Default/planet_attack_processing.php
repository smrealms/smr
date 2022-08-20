<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$account = $session->getAccount();
$player = $session->getPlayer();
$ship = $player->getShip();

if ($player->hasNewbieTurns()) {
	create_error('You are under newbie protection!');
}
if ($player->hasFederalProtection()) {
	create_error('You are under federal protection!');
}
if ($player->isLandedOnPlanet()) {
	create_error('You cannot attack planets whilst on a planet!');
}
if ($player->getTurns() < TURNS_TO_SHOOT_PLANET) {
	create_error('You do not have enough turns to attack this planet!');
}
if (!$ship->hasWeapons() && !$ship->hasCDs()) {
	create_error('What are you going to do? Insult it to death?');
}
if (!$player->canFight()) {
	create_error('You are not allowed to fight!');
}

$planet = $player->getSectorPlanet();
if (!$planet->exists()) {
	create_error('This planet does not exist.');
}
if (!$planet->hasOwner()) {
	create_error('This planet is not claimed.');
}

$planetOwner = $planet->getOwner();

if ($player->forceNAPAlliance($planetOwner)) {
	create_error('You have a planet NAP, you cannot attack this planet!');
}

// take the turns
$player->takeTurns(TURNS_TO_SHOOT_PLANET);


// ********************************
// *
// * P l a n e t   a t t a c k
// *
// ********************************

$results = ['Attackers' => ['TotalDamage' => 0]];

$attackers = $player->getSector()->getFightingTradersAgainstPlanet($player, $planet);

$planet->attackedBy($player, $attackers);

//decloak all attackers
foreach ($attackers as $attacker) {
	$attacker->getShip()->decloak();
}

$totalShieldDamage = 0;
foreach ($attackers as $attacker) {
	$playerResults = $attacker->shootPlanet($planet);
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
$results['Attackers']['Downgrades'] = $planet->checkForDowngrade($downgradeDamage);

$results['Planet'] = $planet->shootPlayers($attackers);

$account->log(LOG_TYPE_PLANET_BUSTING, 'Player attacks planet, the planet does ' . $results['Planet']['TotalDamage'] . ', their team does ' . $results['Attackers']['TotalDamage'] . ' and downgrades: ' . var_export($results['Attackers']['Downgrades'], true), $planet->getSectorID());

// Add this log to the `combat_logs` database table
$db = Smr\Database::getInstance();
$logId = $db->insert('combat_logs', [
	'game_id' => $db->escapeNumber($player->getGameID()),
	'type' => $db->escapeString('PLANET'),
	'sector_id' => $db->escapeNumber($planet->getSectorID()),
	'timestamp' => $db->escapeNumber(Smr\Epoch::time()),
	'attacker_id' => $db->escapeNumber($player->getAccountID()),
	'attacker_alliance_id' => $db->escapeNumber($player->getAllianceID()),
	'defender_id' => $db->escapeNumber($planetOwner->getAccountID()),
	'defender_alliance_id' => $db->escapeNumber($planetOwner->getAllianceID()),
	'result' => $db->escapeObject($results, true),
]);

if ($planet->isDestroyed()) {
	$db->write('UPDATE player SET land_on_planet = \'FALSE\' WHERE sector_id = ' . $db->escapeNumber($planet->getSectorID()) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()));
	$planet->removeOwner();
	$planet->removePassword();

	// Prepare message for planet owners
	$planetAttackMessage = 'The defenses of ' . $planet->getCombatName() . ' have been breached. The planet is lost! [combatlog=' . $logId . ']';
} else {
	$planetAttackMessage = 'Reports from the surface of ' . $planet->getCombatName() . ' confirm that it is under <span class="red">attack</span>! [combatlog=' . $logId . ']';
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
	if (!$player->equals($attacker)) {
		$db->replace('sector_message', [
			'account_id' => $db->escapeNumber($attacker->getAccountID()),
			'game_id' => $db->escapeNumber($attacker->getGameID()),
			'message' => $db->escapeString('[ATTACK_RESULTS]' . $logId),
		]);
	}
}

// If player died they are now in another sector, and thus locks need reset
if ($player->isDead()) {
	saveAllAndReleaseLock(updateSession: false);
	// Grab the lock in the new sector to avoid reloading session
	Smr\SectorLock::getInstance()->acquireForPlayer($player);
}

// If they died on the shot they get to see the results
$container = Page::create('planet_attack.php', skipRedirect: $player->isDead());
$container['sector_id'] = $planet->getSectorID();
$container['results'] = $results;
$container->go();
