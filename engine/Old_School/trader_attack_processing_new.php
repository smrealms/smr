<?

// TODO: Cleanup and finalise
define ('MAX_TURNS', (Globals::getGameSpeed($player->getGameID()) * 400));
define ('MAXIMUM_FLEET_SIZE',10);
define ('NORMAL_HIT', 0);
define ('SHIELD_ON_ARMOR',1);
define ('SHIELD_ON_DRONES',2);
define ('ARMOR_ON_SHIELD',3);
define ('HIT_DEBRIS',4);
define ('FINAL_HIT',5);
define ('WEAPON_MISS',6);

define ('PLAYER_ID', 0);
define ('PLAYER_NAME', 1);
define ('ALLIANCE_ID',2);
define ('RACE_ID', 3);
define ('CREDITS', 4);
define ('TURNS', 5);
define ('ALIGNMENT', 6);
define ('SHIP_ID', 7);
define ('EXPERIENCE', 8);
define ('LEVEL', 9);
define ('SHIELDS', 10);
define ('ARMOR', 11);
define ('DRONES', 12);
define ('DRONES_ORIGINAL', 13);
define ('DCS', 14);
define ('WEAPONS', 15);
define ('RESULTS', 16);
define ('EXPERIENCE_GAINED', 17);
define ('CREDITS_GAINED', 18);
define ('KILLER', 19);
define ('KILLED', 20);
define ('ALIGNMENT_GAINED',21);
define ('MILITARY_GAINED',22);
define ('TOTAL_DAMAGE',23);
define ('EXPERIENCE_LOST',24);

define ('WEAPON_NAME', 0);
define ('SHIELD_DAMAGE', 1);
define ('ARMOR_DAMAGE', 2);
define ('ACCURACY', 3);

define('MESSAGE_EXPIRES', TIME + 259200);

//treaty stuff
define('NAP',0);
define('DEFEND',1);
define('ASSIST',2);

function process_fleet(&$attackers,&$defenders,&$players,&$weapons) {
	$fleet_size = count($attackers);
	// Process each player in turn
	for($i=0;$i<$fleet_size;++$i) {
		process_attacker($attackers[$i],$defenders,$players,$weapons);
	}
}

function process_attacker($attacker,&$defenders,&$players,&$weapons) {
	$num_weapons = count($players[$attacker][WEAPONS]);
	// Process each weapon in turn
	for($i=0;$i<$num_weapons;++$i) {
		// Select a random defender
		$defender = $defenders[array_rand($defenders)];
		$result = process_weapon($players[$attacker][WEAPONS][$i],$attacker,$defender,$players,$weapons);

		// Take the appropriate damage from the defender
		$players[$defender][SHIELDS] -= $result[0];
		$players[$defender][DRONES] -= floor($result[1]/3);
		$players[$defender][ARMOR] -= $result[2];

		$result[5] = $defender;

		$players[$attacker][RESULTS][] = $result;

		// Did they kill somebody?
		if($result[4] == FINAL_HIT) {
			// Record this for news and messages later
			$players[$defender][KILLER] = $attacker;
			$players[$attacker][KILLED][] = $defender;
		}
	}
}

function process_weapon($weapon,$attacker,$defender,&$players,&$weapons) {
	$result = array(0,0,0,0,NORMAL_HIT);

	// Does the weapon hit?
	if($weapon) {
		$hit = $weapons[$weapon][ACCURACY] + 
			($players[$attacker][LEVEL] - ($players[$defender][LEVEL] * 0.5));

		if(mt_rand(0,99) > $hit) {
			$result[4] = WEAPON_MISS;
			return $result;
		}
	}

	// Drones are weapon id 0 and their damage rolls over
	if(!$weapon) {
		// Calculate how many drones actual fire
        $drones_percentage = ((mt_rand(3,54)+mt_rand($players[$attacker][LEVEL]/4,$players[$attacker][LEVEL]))-($players[$defender][LEVEL]-$players[$attacker][LEVEL])/3)/100;

		if($drones_percentage < 0) $drones_percentage = 0;
		else if($drones_percentage > 1) $drones_percentage = 1;

		$result[3] = ceil($players[$attacker][DRONES_ORIGINAL] * $drones_percentage);
    
		if(!$players[$defender][DCS]) {
			$potential_damage = 2 * $result[3];
		}
		else {
			// Drones only do 1.5 damage against DCS carrying players
			$potential_damage = floor(1.5 * $result[3]);
		}

		// Yes, they can miss with all drones
		if(!$potential_damage) {
			$result[4] = WEAPON_MISS;
			return $result;
		}
	}
	else {
		$potential_damage = $weapons[$weapon][SHIELD_DAMAGE];
	}

	// Are they already dead?
	if($players[$defender][ARMOR] == 0 ) {
		$result[4] = HIT_DEBRIS;
		return $result;
	}

	// Try to hit shields
	if($players[$defender][SHIELDS] != 0 ) {
		// Does the weapon do shield damage?
		if($potential_damage) {
			// Have we produced more damage than there are shields remaining?
			if($potential_damage >= $players[$defender][SHIELDS]) {
				$result[0] =  $players[$defender][SHIELDS];
			}
			else {
				$result[0] = $potential_damage;
			}

			// If it's an ordinary weapon or drones are out of damage then return
			if($weapon || $result[0] == $potential_damage) {
				$result[4] = NORMAL_HIT;
				return $result;
			}
		}
		else {
			$result[4] = ARMOR_ON_SHIELD;
			return $result;
		}
	}

	// If a drone shot then adjust damage so we work in units of 1 drone
	if(!$weapon) {
		$potential_damage -= $result[0];
		if(!$players[$defender][DCS]) {
			$potential_damage = 2 * floor($potential_damage/2);
		}
		else {
			// DCS reduces damage by 75% (We can't take off 0.5 of anything)
			$potential_damage = floor(1.5 * floor($potential_damage/1.5));
		}
		if($potential_damage == 0) {
			$result[4] = NORMAL_HIT;
			return $result;
		}
	}
	else {
		$potential_damage = $weapons[$weapon][ARMOR_DAMAGE];
	}

	// No shields left, try to hit their drones
	if($players[$defender][DRONES] != 0 ) {
		// Does the weapon do armor damage?
		if($potential_damage) {
			// Have we produced more damage than there are shields remaining?
			if($potential_damage >= $players[$defender][DRONES] * 3) {
				$result[1] =  $players[$defender][DRONES] * 3;
			}
			else {
				$result[1] = $potential_damage;
			}
			// If it's an ordinary weapon or drones are out of damage then return
			if($weapon || $result[1] == $potential_damage) {
				$result[4] = NORMAL_HIT;
				return $result;
			}
		}
		else {
			$result[4] = SHIELD_ON_DRONES;
			return $result;
		}
	}

	// If a drone shot then adjust damage so we work in units of 1 drone
	if(!$weapon) {
		$potential_damage -= $result[1];
		if(!$players[$defender][DCS]) {
			$potential_damage = 2 * floor($potential_damage/2);
		}
		else {
			// DCS reduces damage by 75% (We can't take off 0.5 of anything)
			$potential_damage = floor(1.5 * floor($potential_damage/1.5));
		}
		if($potential_damage == 0) {
			$result[4] = NORMAL_HIT;
			return $result;
		}
	}
	else {
		$potential_damage = $weapons[$weapon][ARMOR_DAMAGE];
	}

	// No drones left, try to hit their armour
	if($players[$defender][ARMOR] != 0 ) {
		// Does the weapon do armor damage?
		if($potential_damage) {
			// Have we produced more damage than there are shields remaining?
			if($potential_damage >= $players[$defender][ARMOR]) {
				$result[2] = $players[$defender][ARMOR];
				// Final hit
				$result[4] = FINAL_HIT;
			}
			else {
				$result[2] = NORMAL_HIT;
				$result[2] = $potential_damage;
			}
		}
		else {
			$result[4] = SHIELD_ON_ARMOR;
		}
	}

	return $result;
}

function protected_rating($account_id,&$players,&$weapons) {
	// Calculate their attack rating
	$weapons_damage = 0;
	foreach($players[$account_id][WEAPONS] as $weapon) {
		// Ignore drones (Weapon id 0)
		if($weapon) {
			$weapons_damage += ($weapons[$weapon][1] + $weapons[$weapon][2]);
		}

	}
	//$rating = round($weapons_damage/40 + $players[$account_id][DRONES_ORIGINAL]/50);
	$maxDronesPercent = (35 + $players[$account_id][LEVEL] * .6 + ($players[$account_id][LEVEL] - 1) * .4 + 15) * .01;
	$maxDrones = $maxDronesPercent * $players[$account_id][DRONES_ORIGINAL];
	$rating = round((($weapons_damage + $maxDrones * 2) / 40));
	// Aligment adjusts their permitted attack rating for protection
	if($players[$account_id][ALIGNMENT] > 0) {
		$alignment_mod = floor($players[$account_id][ALIGNMENT]/150);
	}
	else {
		$alignment_mod = ceil($players[$account_id][ALIGNMENT]/150);
	}

	if($rating == 0 || ($rating <= (3 + $alignment_mod) && $rating <= 8)) {
		return TRUE;
	}

	return FALSE;
}

function build_hqs(&$races) {
	global $db,$session;

	// We know that race HQs have a location id of 101 + race_id
	foreach($races as $race_id) {
		$temp[] = $race_id + 101; 
	}

	$db->query('SELECT location_type_id,sector_id FROM location WHERE location_type_id IN (' . implode($temp,',') . ') AND game_id=' . SmrSession::$game_id . ' LIMIT ' . count($temp));
	while($db->next_record()) {
		$hqs[$db->f('location_type_id') - 101] = $db->f('sector_id');
	}
	return $hqs;
}

function build_relations(&$race_ids) {
	global $db,$session;
	$race_in = implode(',',$race_ids);
	$db->query('SELECT race_id_1,race_id_2,relation FROM race_has_relation WHERE race_id_1 IN (' . $race_in . ') AND race_id_2 IN (' . $race_in . ') AND game_id=' . SmrSession::$game_id . ' LIMIT ' . (count($race_ids)*2));

	while($db->next_record()) {
		$relations[$db->f('race_id_1')][$db->f('race_id_2')] = $db->f('relation');	
	}
	
	return $relations;
}

function build_bounties(&$account_ids) {
	global $db,$session;	

	$bounties = array();
		
	$db->query('SELECT bounty_id,account_id,amount,type FROM bounty WHERE account_id IN (' . implode(',',$account_ids) . ') AND claimer_id=0 AND game_id=' . SmrSession::$game_id);

	while($db->next_record()) {
		if($db->f('type') == 'HQ') {
			$bounties[$db->f('account_id')][0] = array(
				(int)$db->f('bounty_id'),
				0,
				(int)$db->f('amount')
			);
		}
		else {
			$bounties[$db->f('account_id')][1] = array(
				(int)$db->f('bounty_id'),
				0,
				(int)$db->f('amount')
			);
		}
	}
	
	return $bounties;
}

function build_ship_names($account_ids){
	global $db,$session;

	$ship_names = array();
	
	// Named ships and ship images
	$db->query('SELECT account_id,ship_name FROM ship_has_name WHERE account_id IN (' . implode(',',$account_ids) . ') AND game_id=' . SmrSession::$game_id . ' LIMIT ' . count($account_ids));

	while($db->next_record()) {
			$ship_names[$db->f('account_id')] = $db->f('ship_name');
	}
	
	return $ship_names;
}

function build_ships($ship_ids) {
	global $db;

	$db->query('SELECT ship_type_id,cost,speed FROM ship_type WHERE ship_type_id IN (' . implode(',',$ship_ids) . ') LIMIT ' . count($ship_ids));

	while($db->next_record()) {
		$ships[$db->f('ship_type_id')] = array($db->f('cost'),$db->f('speed'));
	}
	
	return $ships;
}

function build_fleets(&$players,&$weapons,$attackers,$defenders) {
	global $db,$session,$player,$var;
	
	$player_ids = array_keys($players);
	$fleets = array();
		
	// Is there a fed beacon in the sector?
	$db->query('SELECT
	location_type_id
	FROM
	location
	WHERE location_type_id=201
	AND sector_id=' . $player->getSectorID() . '
	AND game_id=' . SmrSession::$game_id . '
	LIMIT 1');

	if($db->next_record()) {
		$have_beacon = TRUE;

		$db->query('SELECT account_id FROM ship_has_cargo WHERE good_id IN (5,9,12) AND game_id=' . SmrSession::$game_id . ' AND account_id IN (' . implode(',',$player_ids) . ')');
		
		while($db->next_record()) {
			$illegal_goods[$db->f('account_id')] = TRUE;
		}
	}
	else {
		$have_beacon = FALSE;
	}

	foreach ($player_ids as $account_id) {
		// Remove players that are fed protected from the fighting
		if(
			$have_beacon &&
			!isset($illegal_goods[$account_id]) &&
			protected_rating($account_id,$players,$weapons)
		){
			unset ($players[$account_id]);
		}
		else if($account_id != $player->getAccountID() && $account_id != $var['target']) {
			// We add the player and target to the fleets after capping
			if(in_array($players[$account_id][ALLIANCE_ID], $attackers)) {
				$fleets[0][] = $account_id;
			} else {
				$fleets[1][] = $account_id;
			}				
		}
	}


	// Add the inital combatants to their respective fleets
	$fleets[0][] = (int)SmrSession::$account_id;
	$fleets[1][] = (int)$var['target'];

	// Shuffle for random firing order
	shuffle($fleets[0]);
	shuffle($fleets[1]);
	
	return $fleets;
}

function build_results(&$players,&$fleets,&$weapons,&$killed_ids,&$killer_ids) {
	for($i=0;$i<2;++$i) {
		if($i==0) {
			$results .= '<h1>Attacker Results</h1><br>';
		}
		else {
			$results .= '<img src="images/creonti_cruiser.jpg" alt="Creonti Cruiser" title="Creonti Cruiser"><br><br><h1>Defender Results</h1><br>';
		}
		$fleet_damage = 0;
		
		foreach($fleets[$i] as $attacker) {
			$total_damage = 0;
			$weapon = 0;
			foreach($players[$attacker][RESULTS] as $result) {
				$total_damage += ($result[0] + $result[1] + $result[2]);
				$results .=  $players[$attacker][PLAYER_NAME];
				if(!$players[$attacker][WEAPONS][$weapon]) {
					if($result[3]) {
						$results .= ' launches <span class="yellow">' . $result[3] . '</span> drones';
					}
					else {
						$results .= ' fails to launch their drones';
					}
				}
				else {
					$results .= ' fires their ';
					$results .= $weapons[$players[$attacker][WEAPONS][$weapon]][WEAPON_NAME];
				}
	
				$results .= ' at ';
				if($result[4] == HIT_DEBRIS) {
					$results .= ' the debris that was once ';
				}
	
				$results .= $players[$result[5]][PLAYER_NAME];
	
	
				if($result[4] == ARMOR_ON_SHIELD) {
					$results .= ' which is deflected by their shields.';
				}
				else if ($result[4] == SHIELD_ON_DRONES) {
					$results .= ' which proves ineffective against their combat drones.';
				}
				else if ($result[4] == SHIELD_ON_ARMOR) {
					$results .= ' which washes harmlessly over their hull.';
				}
				else if ($result[4] == WEAPON_MISS && $players[$attacker][WEAPONS][$weapon]) {
					$results .= ' and misses.';
				}
				else if($result[4] == HIT_DEBRIS || (!$players[$attacker][WEAPONS][$weapon] && !$result[3])) {
					$results .= '.';
				}
				else {
					$results .= ' destroying ';
					if($result[0]) {
						$results .= '<span class="cyan">' . $result[0] . '</span> shields';
					}
					if($result[1]) {
						if($result[0] && !$result[2]) {
							$results .= ' and ';
						}
						else if($result[0] && $result[2]) {
							$results .= ', ';
						}
						$results .= '<span class="yellow">' . floor($result[1]/3) . '</span> drones';
					}
					if($result[2]) {
						if($result[0] || $result[1]) {
							$results .= ' and ';
						}
						$results .= '<span class="red">' . $result[2] . '</span> plates of armour';
					}
					$results .= '.';
				}
	
				// Has their opponent been destroyed?
				if($result[4] == FINAL_HIT) {
					$results .= '<br>' . $players[$result[5]][PLAYER_NAME];
					$results .= ' has been <span class="red">DESTROYED!</span><br>';
					$results .= $players[$attacker][PLAYER_NAME] . ' salvages <span class="yellow">';
					$results .= number_format($players[$result[5]][CREDITS] + $players[$result[5]][CREDITS_GAINED]) . '</span> credits from the wreckage.';
	
					// We keep track of money here so none gets lost. E.g X kills Y who killed Z
					$players[$attacker][CREDITS_GAINED] += ($players[$result[5]][CREDITS] + $players[$result[5]][CREDITS_GAINED]);
	
					$killed_ids[] = $result[5];
					$killer_ids[] = $attacker;
				}
				$results .= '<br>';
				++$weapon;
			}
	
			$results .= $players[$attacker][PLAYER_NAME];
			if($total_damage > 0) {
				$results .=  ' hits for a total of <span class="red">';
				$results .= $total_damage . '</span> damage in this round of combat.<br><br>';
			}
			else {
				$results .= ' does no damage this round. Maybe they should go back to the academy.<br><br>';
			}
			$players[$attacker][TOTAL_DAMAGE] = $total_damage;
			$fleet_damage += $total_damage;
	
			// TODO: Move this
			// 25% of damage gets converted to experience
			$players[$attacker][EXPERIENCE_GAINED] += ceil($total_damage * 0.25);
		}
	
		$results .= 'This fleet ';
		if($fleet_damage > 0) {
			$results .= 'hits for a total of <span class="red">';
			$results .= $fleet_damage;
			$results .= '</span> damage in this round of combat.<br><br>';
		}
		else {
			$results .= 'does no damage this round. You call that a fleet? They need a better recruiter.<br><br>';
		}
	}
	
	return $results;
}

function process_unread_messages(&$players,$killed_id) {

	global $session,$player;
	
	$killer_id = $players[$killed_id][KILLER];
	
	$msg .= '(' . $killed_id . ',' . $player->getGameID() . ',2)';
	$msg .= ',(' . $killer_id . ',' . $player->getGameID() . ',2)';

	return $msg;	
}

function process_bounty(&$players,&$killed_id,&$bounties) {
	
}

function update_bounties(&$bounties) {
	global $db,$session;	
	
	$bounties_in = '';

	foreach($bounties as $account_id => $account_bounties) {
		foreach($account_bounties as $type => $bounty) {
			// If there is bounty id then we're updating a bounty, not setting one
			if(isset($bounty[0]) && $bounty[2] > 0) {
				$db->query('
					UPDATE bounty 
					SET 
					amount=' . $bounty[2] . ',
					claimer_id=' . $bounty[1] . '
					WHERE
					bounty_id=' . $bounty[0] . '
					LIMIT 1'
				);
			}
			// Otherwise we must insert a new bounty
			// We must only insert for bounties that have an amount greater than 0
			else if (isset($bounty[2]) && $bounty[2] > 0){
				if(!empty($bounties_in)) {
					$bounties_in .= ',';
				}
				$bounties_in .= '(';
				$bounties_in .=  $account_id . ',';
				$bounties_in .= SmrSession::$game_id . ',';
				if($type == 0) {
					$bounties_in .= '\'HQ\',';
				}
				else {
					$bounties_in .= '\'UG\',';
				}
				$bounties_in .= $bounty[2] . ',';
				$bounties_in .= $bounty[1] . ',';

				$bounties_in .= TIME . ')';
			}
		}	
	}

	if(!empty($bounties_in)) {
		$db->query('INSERT INTO bounty(account_id,game_id,type,amount,claimer_id,time) VALUES ' . $bounties_in);
	}
}

function update_podded(&$killed_ids) {
	global $db,$session;
	// Dealt with hardware, cloaks etc for podded players
	$num_podded = count($killed_ids);

	if($num_podded) {
		$podded_in = implode(',',$killed_ids);

		$db->query('DELETE FROM ship_has_weapon WHERE account_id IN (' . $podded_in . ') AND game_id=' . SmrSession::$game_id);

		$db->query('DELETE FROM ship_has_cargo WHERE account_id IN (' . $podded_in . ') AND game_id=' . SmrSession::$game_id);

		$db->query('DELETE FROM ship_has_illusion WHERE account_id IN (' . $podded_in . ') AND game_id=' . SmrSession::$game_id);

		$db->query('DELETE FROM ship_has_cargo WHERE account_id IN (' . $podded_in . ') AND game_id=' . SmrSession::$game_id);

		$db->query('DELETE FROM player_plotted_course WHERE account_id IN (' . $podded_in . ') AND game_id=' . SmrSession::$game_id . ' LIMIT ' . $num_podded);

		$db->query('DELETE FROM ship_has_hardware WHERE account_id IN (' . $podded_in . ') AND hardware_type_id>4 AND game_id=' . SmrSession::$game_id);

		$db->query('UPDATE ship_has_hardware SET amount=5 WHERE account_id IN (' . $podded_in . ') AND hardware_type_id=3  AND game_id=' . SmrSession::$game_id . ' LIMIT ' . $num_podded);

		$db->query('UPDATE ship_has_hardware SET old_amount=amount WHERE account_id IN (' . $podded_in . ') AND game_id=' . SmrSession::$game_id);
	}
}

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
$fightingPlayers = $sector->getFightingTraders($player,$targetPlayer);


// Cap fleets to the required size
foreach($fightingPlayers as $team => &$teamPlayers)//$i=0;$i<2;++$i)
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
	foreach($teamPlayers as &$teamPlayer)
		$teamPlayer->getShip()->decloak();
		unset($teamPlayer);
unset($teamPlayers);

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