<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();
$ship = $player->getShip();

$forces = SmrForce::getForce($player->getGameID(), $player->getSectorID(), $var['owner_id']);
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
$bump = match ($var['action']) {
	'attack' => false,
	'bump' => true,
};

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
$db = Smr\Database::getInstance();
$logId = $db->insert('combat_logs', [
	'game_id' => $db->escapeNumber($player->getGameID()),
	'type' => $db->escapeString('FORCE'),
	'sector_id' => $db->escapeNumber($forces->getSectorID()),
	'timestamp' => $db->escapeNumber(Smr\Epoch::time()),
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

$container = Page::create('skeleton.php', 'forces_attack.php');

// If their target is dead there is no continue attack button
if ($forces->exists()) {
	$container['owner_id'] = $forces->getOwnerID();
} else {
	$container['owner_id'] = 0;
}

// If they died on the shot they get to see the results
if ($player->isDead()) {
	$container['override_death'] = true;
	$container['owner_id'] = 0;
}

$container['results'] = $results;
$container->go();
