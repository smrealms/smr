<?
if($player->hasFederalProtection())
{
	$container=array();
	$container['url'] = 'skeleton.php';
	$container['body'] = 'current_sector.php';
	$container['msg'] = '<span class="red bold">ERROR:</span> You are under federal protection.';
	forward($container);
	exit;
}
if($player->getTurns() < 3)
{
	$container=array();
	$container['url'] = 'skeleton.php';
	$container['body'] = 'current_sector.php';
	$container['msg'] = '<span class="red bold">ERROR:</span> You have insufficient turns to perform that action.';
	forward($container);
	exit;
}

$targetPlayer =& SmrPlayer::getPlayer($var['target'],$player->getGameID());

	if($player->traderNAPAlliance($targetPlayer))
	{
		$container=array();
		$container['url'] = 'skeleton.php';
		$container['body'] = 'current_sector.php';
		$container['msg'] = '<span class="red bold">ERROR:</span> Your alliance does not allow you to attack this trader.';
		forward($container);
		exit;
	}
	else if($targetPlayer->isDead())
	{
		$container=array();
		$container['url'] = 'skeleton.php';
		$container['body'] = 'current_sector.php';
		$container['msg'] = '<span class="red bold">ERROR:</span> Target is already dead.';
		forward($container);
		exit;
	}
	else if($targetPlayer->getSectorID() != $player->getSectorID())
	{
		$container=array();
		$container['url'] = 'skeleton.php';
		$container['body'] = 'current_sector.php';
		$container['msg'] = '<span class="red bold">ERROR:</span> Target is no longer in this sector.';
		forward($container);
		exit;
	}
	else if($targetPlayer->hasNewbieTurns())
	{
		$container=array();
		$container['url'] = 'skeleton.php';
		$container['body'] = 'current_sector.php';
		$container['msg'] = '<span class="red bold">ERROR:</span> Target is under newbie protection.';
		forward($container);
		exit;
	}
	else if($targetPlayer->isLandedOnPlanet())
	{
		$container=array();
		$container['url'] = 'skeleton.php';
		$container['body'] = 'current_sector.php';
		$container['msg'] = '<span class="red bold">ERROR:</span> Target is protected by planetary shields.';
		forward($container);
		exit;
	}
	else if($targetPlayer->hasFederalProtection())
	{
		$container=array();
		$container['url'] = 'skeleton.php';
		$container['body'] = 'current_sector.php';
		$container['msg'] = '<span class="red bold">ERROR:</span> Target is under federal protection.';
		forward($container);
		exit;
	}

$sector =& SmrSector::getSector($player->getGameID(),$player->getSectorID(),$player->getAccountID());
$fightingPlayers =& $sector->getFightingTraders($player,$targetPlayer);


// Cap fleets to the required size
foreach($fightingPlayers as $team => &$teamPlayers)
{
	$fleet_size = count($teamPlayers);
	if($fleet_size > MAXIMUM_FLEET_SIZE)
	{
		// We use random key to stop the same people being capped all the time
		for($j=0;$j<$fleet_size-MAXIMUM_FLEET_SIZE;++$j)
		{
			do
			{
				$key = array_rand($teamPlayers);
			} while($player->equals($teamPlayers[$key]) || $targetPlayer->equals($teamPlayers[$key]));
			unset($teamPlayers[$key]);
		}
	}
} unset($teamPlayers);
	
//decloak all fighters
foreach($fightingPlayers as &$teamPlayers)
{
	foreach($teamPlayers as &$teamPlayer)
	{
		$teamPlayer->getShip()->decloak();
	} unset($teamPlayer);
} unset($teamPlayers);

// Take off the 3 turns for attacking
$player->takeTurns(3);
$player->update();

$results = array('Attackers' => array('Traders' => array(), 'TotalDamage' => 0), 
				'Defenders' => array('Traders' => array(), 'TotalDamage' => 0));
foreach($fightingPlayers['Attackers'] as $accountID => &$teamPlayer)
{
	$playerResults =& $teamPlayer->shootPlayers($fightingPlayers['Defenders']);
	$results['Attackers']['Traders'][$teamPlayer->getAccountID()]  =& $playerResults;
	$results['Attackers']['TotalDamage'] += $playerResults['TotalDamage'];
} unset($teamPlayer);
foreach($fightingPlayers['Defenders'] as $accountID => &$teamPlayer)
{
	$playerResults =& $teamPlayer->shootPlayers($fightingPlayers['Attackers']);
	$results['Defenders']['Traders'][$teamPlayer->getAccountID()]  =& $playerResults;
	$results['Defenders']['TotalDamage'] += $playerResults['TotalDamage'];
} unset($teamPlayer);
$ship->removeUnderAttack(); //Don't show attacker the under attack message.

$serializedResults = serialize($results);
$db->query('INSERT INTO combat_logs VALUES(\'\',' . $player->getGameID() . ',\'PLAYER\',' . $player->getSectorID() . ',' . TIME . ',' . $player->getAccountID() . ',' . $player->getAllianceID() . ',' . $var['target'] . ',' . $targetPlayer->getAllianceID() . ',' . $db->escape_string(gzcompress($serializedResults)) . ', \'FALSE\')');
unserialize($serializedResults); //because of references we have to undo this.

$container = array();
$container['url'] = 'skeleton.php';
$container['body'] = 'trader_attack_new.php';

// If their target is dead there is no continue attack button
if(!$targetPlayer->isDead())
	$container['target'] = $var['target'];
else
	$container['target'] = 0;

// If they died on the shot they get to see the results
if($player->isDead())
{
	$container['override_death'] = TRUE;
	$container['target'] = 0;
}

$container['results'] = $serializedResults;
forward($container);

?>