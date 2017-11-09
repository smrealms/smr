<?php
if ($player->hasNewbieTurns())
	create_error('You are under newbie protection!');
if($player->hasFederalProtection())
	create_error('You are under federal protection.');
if($player->isLandedOnPlanet())
	create_error('You cannot attack forces whilst on a planet!');
if(!$player->canFight())
	create_error('You are not allowed to fight!');

require_once(get_file_loc('SmrForce.class.inc'));
$forces =& SmrForce::getForce($player->getGameID(), $player->getSectorID(), $var['owner_id']);

if(!$forces->exists())
	create_error('These forces no longer exist.');
if ($player->getTurns() < $forces->getAttackTurnCost($ship))
	create_error('You do not have enough turns to attack these forces!');

$forceOwner =& $forces->getOwner();

if($player->forceNAPAlliance($forceOwner))
	create_error('You have a force NAP, you cannot attack these forces!');

// take the turns
$player->takeTurns($forces->getAttackTurnCost($ship), 1);

// delete plotted course
$player->deletePlottedCourse();

// send message if scouts are present
if ($forces->hasSDs()) {
	$message = 'Your forces in sector '.$forces->getSectorID().' are being attacked by '.$player->getPlayerName();
	$forces->ping($message, $player);
}

// ********************************
// *
// * F o r c e s   a t t a c k
// *
// ********************************

$results = array('Attackers' => array('TotalDamage' => 0),
				'Forces' => array(),
				'Forced' => false);

$sector =& $player->getSector();
$attackers =& $sector->getFightingTradersAgainstForces($player, $forces);

//decloak all attackers
foreach($attackers as &$attacker) {
	$attacker->getShip()->decloak();
	$attacker->setLastSectorID(0);
} unset($attacker);

$results['Attackers'] = array('TotalDamage' => 0);
foreach($attackers as &$attacker) {
	$playerResults =& $attacker->shootForces($forces);
	$results['Attackers']['Traders'][$attacker->getAccountID()]  =& $playerResults;
	$results['Attackers']['TotalDamage'] += $playerResults['TotalDamage'];
} unset($attacker);

$results['Forces'] =& $forces->shootPlayers($attackers,false);
$forces->updateExpire();

$ship->removeUnderAttack(); //Don't show attacker the under attack message.

$serializedResults = serialize($results);
$db->query('INSERT INTO combat_logs VALUES(\'\',' . $db->escapeNumber($player->getGameID()) . ',\'FORCE\',' . $db->escapeNumber($forces->getSectorID()) . ',' . $db->escapeNumber(TIME) . ',' . $db->escapeNumber($player->getAccountID()) . ',' . $db->escapeNumber($player->getAllianceID()) . ',' . $db->escapeNumber($forceOwner->getAccountID()) . ',' . $db->escapeNumber($forceOwner->getAllianceID()) . ',' . $db->escapeBinary(gzcompress($serializedResults)) . ')');
unserialize($serializedResults); //because of references we have to undo this.

$container = create_container('skeleton.php', 'forces_attack.php');

// If their target is dead there is no continue attack button
if($forces->exists())
	$container['owner_id'] = $forces->getOwnerID();
else
	$container['owner_id'] = 0;

// If they died on the shot they get to see the results
if($player->isDead()) {
	$container['override_death'] = TRUE;
	$container['owner_id'] = 0;
}

$container['results'] = $serializedResults;
forward($container);
?>
