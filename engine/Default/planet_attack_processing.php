<?

if ($player->hasNewbieTurns())
	create_error('You are under newbie protection!');
if ($player->hasFederalProtection())
	create_error('You are under federal protection!');
if($player->isLandedOnPlanet())
	create_error('You cannot attack planets whilst on a planet!');
if ($player->getTurns() < 3)
	create_error('You do not have enough turns to attack this planet!');
if(!$ship->hasWeapons() && !$ship->hasCDs())
	create_error('What are you going to do? Insult it to death?');
if(!$player->canFight())
	create_error('You are not allowed to fight!');

require_once(get_file_loc('SmrPlanet.class.inc'));
$planet =& SmrPlanet::getPlanet($player->getGameID(), $player->getSectorID());
if(!$planet->exists())
	create_error('This planet does not exist.');
if(!$planet->isClaimed())
	create_error('This planet is not claimed.');
	
$planetOwner =& $planet->getOwner();

if($player->forceNAPAlliance($planetOwner))
	create_error('You have a planet NAP, you cannot attack this planet!');
	
if ($planet->isDestroyed())
{
	$db->query('UPDATE player SET land_on_planet = \'FALSE\' WHERE sector_id = '.$player->getSectorID().' AND game_id = '.$player->getGameID());
	$planet->removeClaimed();
	$planet->removePassword();
	$planet->update();
	$container=create_container('skeleton.php','planet_attack.php');
	$container['sector_id'] = $planet->getSectorID();
	forward($container);
}

// take the turns
$player->takeTurns(3,0);


// ********************************
// *
// * P o r t   a t t a c k
// *
// ********************************

$results = array('Attackers' => array('TotalDamage' => 0),
				'Forces' => array(),
				'Forced' => false);

require_once(get_file_loc('SmrSector.class.inc'));
$sector =& SmrSector::getSector(SmrSession::$game_id, $player->getSectorID());
$attackers =& $sector->getFightingTradersAgainstPlanet($player, $planet);

$planet->attackedBy($player,$attackers);

//decloak all attackers
foreach($attackers as &$attacker)
{
	$attacker->getShip()->decloak();
} unset($attacker);

foreach($attackers as &$attacker)
{
	$playerResults =& $attacker->shootPlanet($planet);
	$results['Attackers']['Traders'][$attacker->getAccountID()]  =& $playerResults;
	$results['Attackers']['TotalDamage'] += $playerResults['TotalDamage'];
} unset($attacker);
$account->log(7, 'Player attacks planet their team does ' . $results['Attackers']['TotalDamage'], $planet->getSectorID());
$results['Attackers']['Downgrades'] = $planet->checkForDowngrade($results['Attackers']['TotalDamage']);

$results['Planet'] =& $planet->shootPlayers($attackers);

$ship->removeUnderAttack(); //Don't show attacker the under attack message.

$planetAttackMessage = 'Reports from the surface of '.$planet->getDisplayName().' confirm that it is under <span class="red">attack</span>!';
if($planetOwner->hasAlliance())
{
	$db->query('SELECT account_id FROM player WHERE game_id=' . $planetOwner->getGameID() . 
			' AND alliance_id=' . $planetOwner->getAllianceID()); //No limit in case they are over limit - ie NHA
	while ($db->nextRecord())
		SmrPlayer::sendMessageFromPlanet($planet->getGameID(),$db->getField('account_id'),$planetAttackMessage);
}
else
	SmrPlayer::sendMessageFromPlanet($planet->getGameID(),$planetOwner->getAccountID(),$planetAttackMessage);

$planet->update();

$serializedResults = serialize($results);
$db->query('INSERT INTO combat_logs VALUES(\'\',' . $player->getGameID() . ',\'PLANET\',' . $player->getSectorID() . ',' . TIME . ',' . $player->getAccountID() . ',' . $player->getAllianceID() . ','.$planetOwner->getAccountID().',' . $planetOwner->getAllianceID() . ',' . $db->escapeBinary(gzcompress($serializedResults)) . ', \'FALSE\')');
unserialize($serializedResults); //because of references we have to undo this.

$container = array();
$container['url'] = 'skeleton.php';
$container['body'] = 'planet_attack.php';
$container['sector_id'] = $planet->getSectorID();

// If they died on the shot they get to see the results
if($player->isDead())
{
	$container['override_death'] = TRUE;
}

$container['results'] = $serializedResults;
forward($container);
?>