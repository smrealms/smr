<?php

if ($player->hasNewbieTurns())
	create_error('You are under newbie protection!');
if($player->hasFederalProtection())
	create_error('You are under federal protection.');
if($player->isLandedOnPlanet())
	create_error('You cannot attack forces whilst on a planet!');
if ($player->getTurns() < 3)
	create_error('You do not have enough turns to attack these forces!');
if(!$player->canFight())
	create_error('You are not allowed to fight!');
	
require_once(get_file_loc('SmrForce.class.inc'));
$forces =& SmrForce::getForce($player->getGameID(), $player->getSectorID(), $var['owner_id']);

if(!$forces->exists())
	create_error('These forces no longer exist.');
	
$forceOwner =& $forces->getOwner();

if($player->forceNAPAlliance($forceOwner))
	create_error('You have a force NAP, you cannot attack these forces!');

// take the turns
$player->takeTurns(3,1);

// delete plotted course
$player->deletePlottedCourse();

// send message if scouts are present
if ($forces->hasSDs())
{
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

require_once(get_file_loc('SmrSector.class.inc'));
$sector =& SmrSector::getSector(SmrSession::$game_id, $player->getSectorID());
$attackers =& $sector->getFightingTradersAgainstForces($player, $forces);

//decloak all attackers
foreach($attackers as &$attacker)
{
	$attacker->getShip()->decloak();
} unset($attacker);

foreach($attackers as &$attacker)
{
	$playerResults =& $attacker->shootForces($forces);
	$results['Attackers']['Traders'][$attacker->getAccountID()]  =& $playerResults;
	$results['Attackers']['TotalDamage'] += $playerResults['TotalDamage'];
} unset($attacker);

$results['Forces'] =& $forces->shootPlayers($attackers,false);

$ship->removeUnderAttack(); //Don't show attacker the under attack message.
$forces->updateExpire();

$serializedResults = serialize($results);
$db->query('INSERT INTO combat_logs VALUES(\'\',' . $player->getGameID() . ',\'FORCE\',' . $forces->getSectorID() . ',' . TIME . ',' . $player->getAccountID() . ',' . $player->getAllianceID() . ',' . $forceOwner->getAccountID() . ',' . $forceOwner->getAllianceID() . ',' . $db->escapeBinary(gzcompress($serializedResults)) . ', \'FALSE\')');
unserialize($serializedResults); //because of references we have to undo this.

$container = array();
$container['url'] = 'skeleton.php';
$container['body'] = 'forces_attack.php';

// If their target is dead there is no continue attack button
if($forces->exists())
	$container['owner_id'] = $forces->getOwnerID();
else
	$container['owner_id'] = 0;

// If they died on the shot they get to see the results
if($player->isDead())
{
	$container['override_death'] = TRUE;
	$container['owner_id'] = 0;
}

$container['results'] = $serializedResults;
forward($container);

?>