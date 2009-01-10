<?

if ($player->getNewbieTurns() > 0)
	create_error('You are under newbie protection!');

if ($player->getTurns() < 3)
	create_error('You do not have enough turns to attack these forces!');

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

$container = array();
$container['url'] = 'skeleton.php';
$container['body'] = 'forces_attack.php';
$container['continue'] = 'yes';
$container['forced'] = 'no';

// ********************************
// *
// * F o r c e s   a t t a c k
// *
// ********************************

$results = array('Attackers' => array('TotalDamage' => 0),
				'Forces' => array(),
				'Forced' => false);

require_once(get_file_loc('SmrSector.class.inc'));
$sector =& SmrSector::getSector(SmrSession::$game_id, $player->getSectorID(), SmrSession::$account_id);
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

$serializedResults = serialize($results);
$db->query('INSERT INTO combat_logs VALUES(\'\',' . $player->getGameID() . ',\'FORCE\',' . $player->getSectorID() . ',' . TIME . ',' . $player->getAccountID() . ',' . $player->getAllianceID() . ',' . $forceOwner->getAllianceID() . ',' . $forceOwner->getAllianceID() . ',' . $db->escape_string(gzcompress($serializedResults)) . ', \'FALSE\')');
unserialize($serializedResults); //because of references we have to undo this.

$container = array();
$container['url'] = 'skeleton.php';
$container['body'] = 'forces_attack.php';

// If their target is dead there is no continue attack button
if($forces->exists())
	$container['owner_id'] = $var['owner_id'];
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


//
//// recalc forces expiration date
//if($forces->getCDs() == 0 && $forces->getMines() == 0 && $forces->getSDs() == 1) {
//	$days = 2;
//}
//else {
//	$days = ceil(($forces->getCDs() + $forces->getSDs() + $forces->getMines()) / 10);
//}
//if ($days > 5) $days = 5;
//$forces->setExpire(TIME + ($days * 86400));
//

?>