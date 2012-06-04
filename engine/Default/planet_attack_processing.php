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
$sector =& SmrSector::getSector(SmrSession::$game_id, $player->getSectorID(), SmrSession::$account_id);
$attackers =& $sector->getFightingTradersAgainstPlanet($player, $planet);

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
$planet->update();

$serializedResults = serialize($results);
$db->query('INSERT INTO combat_logs VALUES(\'\',' . $player->getGameID() . ',\'PLANET\',' . $player->getSectorID() . ',' . TIME . ',' . $player->getAccountID() . ',' . $player->getAllianceID() . ','.$planetOwner->getAccountID().',' . $planetOwner->getAllianceID() . ',' . $db->escape_string(gzcompress($serializedResults)) . ', \'FALSE\')');
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
//
//
//
////general info
//define ('MAX_TURNS', (Globals::getGameSpeed($player->getGameID()) * 400));
//$limit = 10;
//require(get_file_loc('planet_limit.php'));
//define ('MAXIMUM_FLEET_SIZE',$limit);
//
////result info
//define('SHIELD_DMG_DONE', 0);
//define('DRONE_DMG_DONE', 1);
//define('ARMOR_DMG_DONE', 2);
//define('DRONES_FIRED', 3);
//define('RESULT_OF_WEAPON', 4);
//define('DRONES_HIT_BEHIND_SHIELDS',5);
//define('TARGET',6);
//
////result[4]
//define('NORMAL_HIT', 0);
//define('SHIELD_ON_DRONES',1);
//define('ARMOR_ON_SHIELD',2);
//define('PLANET_DEAD',3);
//define('FINAL_HIT',4);
//define('WEAPON_MISS',5);
//define('ALREADY_DEAD',6);
//
////player array info
//define('PLAYER_ID', 0);
//define('PLAYER_NAME', 1);
//define('ALLIANCE_ID',2);
//define('RACE_ID', 3);
//define('CREDITS', 4);
//define('TURNS', 5);
//define('ALIGNMENT', 6);
//define('SHIP_ID', 7);
//define('EXPERIENCE', 8);
//define('LEVEL', 9);
//define('SHIELDS', 10);
//define('ARMOR', 11);
//define('DRONES', 12);
//define('DRONES_ORIGINAL', 13);
//define('DCS', 14);
//define('WEAPONS', 15);
//define('RESULTS', 16);
//define('EXPERIENCE_GAINED', 17);
//define('KILLER', 18); 
//define('TOTAL_DAMAGE',19);
//define('PERSONAL_DISPLAY', 20);
//
////weapon array info
//define('WEAPON_NAME', 0);
//define('SHIELD_DAMAGE', 1);
//define('ARMOR_DAMAGE', 2);
//define('ACCURACY', 3);
//
////planet array info
//define('PLANET_SHIELDS', 0);
//define('PLANET_DRONES', 1);
//define('GENERATORS', 2);
//define('HANGARS', 3);
//define('TURRETS', 4);
//define('PLANET_LEVEL',5);
//define('PLANET_RESULTS',6);
//define('OWNER',7);
//define('PLANET_NAME',8);
//
////more general
//define('MESSAGE_EXPIRES', TIME + 259200);
//
////final results info
//define('PLANET_DISPLAY',0);
//define('PLAYER_DISPLAY',1);
//define('TOTAL_PLAYER_DMG',2);
//define('TOTAL_PLANET_DMG',3);
//define('DMG_TO_PLAYER',4);
//define('DEBUG',0);
//function getPlanetArray()
//{
//	if (DEBUG) $PHP_OUTPUT.=('Entered planet array<br />');
//	global $db, $player, $account;
//	$db->query('SELECT  * FROM planet WHERE sector_id = ' . $player->getSectorID() . ' AND game_id = ' . $player->getGameID());
//	if ($db->nextRecord()) {
//		$planet = array($db->getField('shields'),
//			$db->getField('drones'),
//			0,0,0,0,array(),$db->getField('owner_id'),$db->getField('planet_name'));
//		$db->query('SELECT * FROM planet_has_building WHERE sector_id = ' . $player->getSectorID() . ' AND game_id = ' . $player->getGameID());
//		while ($db->nextRecord())
//		{
//			switch($db->getField('construction_id'))
//			{
//				case 1:
//					$planet[GENERATORS] = $db->getField('amount');
//					break;
//				case 2:
//					$planet[HANGARS] = $db->getField('amount');
//					break;
//				case 3:
//					$planet[TURRETS] = $db->getField('amount');
//					break;
//				default:
//					break;
//			}
//		}
//		$planet[PLANET_LEVEL] = ($planet[GENERATORS] + $planet[HANGARS] + $planet[TURRETS]) / 3;
//		if (DEBUG) $PHP_OUTPUT.=('Level set to ' . $planet[PLANET_LEVEL] . '<br />');
//		$planet[PLANET_RESULTS] = array();
//	} else {
//		create_error('Planet does not exist');
//	}
//	if ($planet[PLANET_DRONES] == 0 && $planet[PLANET_SHIELDS] == 0)
//	{
//		$db->query('UPDATE player SET land_on_planet = \'FALSE\' WHERE sector_id = '.$player->getSectorID().' AND game_id = '.$player->getGameID());
//		$db->query('UPDATE planet SET owner_id = 0, password = \'\' WHERE sector_id = '.$player->getSectorID().' AND game_id = '.$player->getGameID());
//		create_error('That planet is already destroyed');
//	}
//	return $planet;
//}
//function getPlayerArray()
//{
//	if (DEBUG) $PHP_OUTPUT.=('Entered player array<br />');
//	global $db, $player, $account;
//	//insert our trigger into the players array
//	$query = 'SELECT * FROM player WHERE account_id = ' . $player->getAccountID() . ' AND game_id = ' . $player->getGameID();
//	$db->query($query);
//	$db->nextRecord();
//	$players[$db->getField('account_id')] = array(
//		(int)$db->getField('player_id'),
//		get_colored_text($db->getField('alignment'), stripslashes($db->getField('player_name')) . ' (' . $db->getField('player_id') . ')'),
//		(int)$db->getField('alliance_id'),
//		(int)$db->getField('race_id'),
//		(int)$db->getField('credits'),
//		(int)$db->getField('turns'),
//		(int)$db->getField('alignment'),
//		(int)$db->getField('ship_type_id'),
//		(int)$db->getField('experience'),
//		0,0,0,0,0,false,array(),array(),0,0,0
//	);
//	
//	// Get the galaxy name and id
//	$db->query('SELECT
//	galaxy_id
//	FROM sector
//	WHERE sector_id=' . $player->getSectorID() . '
//	AND game_id=' . SmrSession::$game_id . '
//	LIMIT 1');
//
//	$db->nextRecord();
//	if($db->getField('galaxy_id') < 9) {
//		$protection = TRUE;
//	}
//	else {
//		$protection = FALSE;
//	}
//	//get treaty info
//	$helperAlliances = array($player->getAllianceID());
//	$db->query('SELECT * FROM alliance_treaties 
//				WHERE game_id = '.$player->getGameID().'
//				AND (alliance_id_1 = '.$player->getAllianceID().' OR alliance_id_2 = '.$player->getAllianceID().')
//				AND raid_assist = 1 AND official = \'TRUE\'');
//	while ($db->nextRecord()) {
//		if ($db->getField('alliance_id_1') == $player->getAllianceID()) $helperAlliances[] = $db->getField('alliance_id_2');
//		else $helperAlliances[] = $db->getField('alliance_id_1');
//	}
//	if($player->getAllianceID()) {
//		$query = '
//		SELECT 
//		player.account_id as account_id,
//		player.player_id as player_id,
//		player.player_name as player_name,
//		player.race_id as race_id,
//		player.alignment as alignment,
//		player.ship_type_id as ship_type_id,
//		player.experience as experience,
//		player.credits as credits,
//		player.turns as turns,
//		player.alliance_id as alliance_id
//		FROM player';
//
//		if ($protection) {
//			$query .= ',account_has_stats,account	
//				WHERE account.account_id = player.account_id
//				AND account_has_stats.account_id = player.account_id';
//
//			if($account->get_rank() > BEGINNER || $account->veteran == 'TRUE') {
//				$query2 = ' AND (
//				(account_has_stats.kills >= 15 OR account_has_stats.experience_traded >= 60000) OR 
//				(account_has_stats.kills >= 10 AND account_has_stats.experience_traded >= 40000)
//				OR account.veteran=\'TRUE\')';
//			}
//			else {
//				$query2 = ' AND (
//				(account_has_stats.kills < 15 AND account_has_stats.experience_traded < 60000) OR 
//				(account_has_stats.kills < 10 AND account_has_stats.experience_traded < 40000)
//				) AND account.veteran=\'FALSE\'';
//			}
//			$query2 .= ' AND ';
//		}
//		else {
//			$query2 = ' WHERE ';
//		}
//
//		$query .= $query2 . 'player.sector_id=' . $player->getSectorID() . '
//			AND player.account_id!=' . SmrSession::$account_id . '
//			AND player.game_id=' . SmrSession::$game_id . ' 
//			AND player.land_on_planet=\'FALSE\' 
//			AND player.newbie_turns=0
//			AND player.last_cpl_action>' .  (TIME - 259200);
//
//		$query .= ' AND player.alliance_id IN (' . implode(',',$helperAlliances) . ')';
//
//		$db->query($query);
//
//		while($db->nextRecord()) {
//			$players[$db->getField('account_id')] = array(
//				(int)$db->getField('player_id'),
//				get_colored_text($db->getField('alignment'),stripslashes($db->getField('player_name')) . ' (' . $db->getField('player_id') . ')'),
//				(int)$db->getField('alliance_id'),
//				(int)$db->getField('race_id'),
//				(int)$db->getField('credits'),
//				(int)$db->getField('turns'),
//				(int)$db->getField('alignment'),
//				(int)$db->getField('ship_type_id'),
//				(int)$db->getField('experience'),
//				0,0,0,0,0,false,array(),array(),0,0,0
//			);
//		}
//	}
//	// Figure out everyone's level
//	$db->query('SELECT level_id,requirement FROM level ORDER BY requirement DESC');
//	while($db->nextRecord()) {
//		$levels[$db->getField('level_id')] = $db->getField('requirement');
//	}
//	
//	$num_players = count($players);
//	$player_ids = array_keys($players);
//	$num_levels = count($levels);
//	$level_ids = array_keys($levels);
//	
//	for($i=0;$i<$num_players;++$i) {
//		for($j=0;$j<$num_levels;++$j) {
//			if($levels[$level_ids[$j]] <= $players[$player_ids[$i]][EXPERIENCE]) {
//				$players[$player_ids[$i]][LEVEL] = $level_ids[$j];
//				break;
//			}
//		}
//	}
//	
//	// Everyone involved gets decloaked if they fire or not
//	$db->query('DELETE FROM ship_is_cloaked WHERE account_id IN (' . implode(',',$player_ids) . ') AND game_id=' . SmrSession::$game_id);
//	
//	return $players;
//	
//}
//function build_ships($ship_ids) {
//	if (DEBUG) $PHP_OUTPUT.=('Build Ships<br />');
//	global $db;
//	$db->query('SELECT ship_type_id,cost,speed FROM ship_type WHERE ship_type_id IN (' . implode(',',$ship_ids) . ') LIMIT ' . count($ship_ids));
//	while($db->nextRecord()) {
//		$ships[$db->getField('ship_type_id')] = array($db->getField('cost'),$db->getField('speed'));
//	}
//	return $ships;
//}
//function build_hqs(&$races) {
//	if (DEBUG) $PHP_OUTPUT.=('Build HQ<br />');
//	global $db;
//	// We know that race HQs have a location id of 101 + race_id
//	$temp = array();
//	foreach($races as $race_id) {
//		$temp[] = $race_id + 101; 
//	}
//	$db->query('SELECT location_type_id,sector_id FROM location WHERE location_type_id IN (' . implode($temp,',') . ') AND game_id=' . SmrSession::$game_id . ' LIMIT ' . count($temp));
//	while($db->nextRecord()) {
//		$hqs[$db->getField('location_type_id') - 101] = $db->getField('sector_id');
//	}
//	return $hqs;
//}
//function getHardware(&$players)
//{
//	if (DEBUG) $PHP_OUTPUT.=('Get Hardware<br />');
//	global $db, $player, $account;
//	$players_in = implode(',',array_keys($players));
//	
//	$db->query('SELECT account_id,weapon_type_id FROM ship_has_weapon WHERE account_id IN (' . $players_in . ') AND game_id=' . SmrSession::$game_id . ' ORDER BY order_id ASC');
//	$weapons = array();
//	while($db->nextRecord()) {
//		$weapons[] = $db->getField('weapon_type_id');
//		$players[$db->getField('account_id')][WEAPONS][] = (int)$db->getField('weapon_type_id');
//	}
//	
//	$db->query('SELECT hardware_type_id,account_id,amount FROM ship_has_hardware WHERE account_id IN (' . $players_in . ') AND (hardware_type_id=' . HARDWARE_SHIELDS . ' OR hardware_type_id=' . HARDWARE_ARMOR . ' OR hardware_type_id=' . HARDWARE_COMBAT . ' OR hardware_type_id=' . HARDWARE_DCS . ') AND game_id=' . SmrSession::$game_id);
//	
//	while($db->nextRecord()) {
//		switch($db->getField('hardware_type_id')) {
//			case(HARDWARE_SHIELDS):
//				$players[$db->getField('account_id')][SHIELDS] = (int)$db->getField('amount');
//				break;
//			case(HARDWARE_ARMOR):
//				$players[$db->getField('account_id')][ARMOR] = (int)$db->getField('amount');
//				break;
//			case(HARDWARE_COMBAT):
//				$players[$db->getField('account_id')][DRONES] = (int)$db->getField('amount');
//				// They fire the same amount of drones they start the round with
//				$players[$db->getField('account_id')][DRONES_ORIGINAL] = (int)$db->getField('amount');
//				// Drones count as a weapon. It's important they are last in the order
//				if($db->getField('amount')) {
//					$players[$db->getField('account_id')][WEAPONS][] = 0;
//				}
//				break;
//			case(HARDWARE_DCS):
//				if($db->getField('amount')) {
//					$players[$db->getField('account_id')][DCS] = TRUE;
//				}
//				break;
//		}
//	}
//	return array_unique($weapons);
//}
//function getWeapons($weapon_ids)
//{
//	if (DEBUG) $PHP_OUTPUT.=('Get Weps<br />');
//	global $db;
//	$weapons = array();
//	if (!sizeof($weapon_ids)) return $weapons;
//	$db->query('SELECT weapon_type_id,weapon_name,shield_damage,armor_damage,accuracy FROM weapon_type WHERE weapon_type_id IN (' . implode(',',$weapon_ids) . ') LIMIT ' . count($weapon_ids));
//	
//	while($db->nextRecord()) {
//		$weapons[$db->getField('weapon_type_id')] = array(
//												$db->getField('weapon_name'),
//												(int)round($db->getField('shield_damage') / 5),
//												(int)round($db->getField('armor_damage') / 5),
//												(int)$db->getField('accuracy')
//												);
//	}	
//	return $weapons;
//}
//function getFleet(&$players,&$weapons) {
//	if (DEBUG) $PHP_OUTPUT.=('Get Fleet<br />');
//	global $db,$player;
//	
//	$player_ids = array_keys($players);
//	$fleet = array();
//		
//	// Is there a fed beacon in the sector?
//	$db->query('SELECT
//	location_type_id
//	FROM
//	location
//	WHERE location_type_id=201
//	AND sector_id=' . $player->getSectorID() . '
//	AND game_id=' . SmrSession::$game_id . '
//	LIMIT 1');
//
//	if($db->nextRecord()) {
//		$have_beacon = TRUE;
//
//		$db->query('SELECT account_id FROM ship_has_cargo WHERE good_id IN (5,9,12) AND game_id=' . SmrSession::$game_id . ' AND account_id IN (' . implode(',',$player_ids) . ')');
//		
//		while($db->nextRecord()) {
//			$illegal_goods[$db->getField('account_id')] = TRUE;
//		}
//	}
//	else {
//		$have_beacon = FALSE;
//	}
//
//	foreach ($player_ids as $account_id) {
//		// Remove players that are fed protected from the fighting
//		if(
//			$have_beacon &&
//			!isset($illegal_goods[$account_id]) &&
//			protected_rating($account_id,$players,$weapons)
//		){
//			// Player and their target must not have dropped into fed protection
//			if($account_id == $player->getAccountID()) {
//				create_error('You are under federal protection');
//			}
//			unset ($players[$account_id]);
//		}
//		else if($account_id != $player->getAccountID()) {
//			// We add the player and target to the fleet after capping
//			if($players[$account_id][ALLIANCE_ID] == $player->getAllianceID()) {
//				$fleet[] = $account_id;
//			}
//			else {
//				$fleet[] = $account_id;
//			}				
//		}
//	}
//
//	// Cap fleet to the required size
//	$fleet_size = count($fleet);
//	if($fleet_size > (MAXIMUM_FLEET_SIZE - 1)) {
//		// We shuffle to stop the same people being capped all the time
//		shuffle($fleet);
//		$temp = array();
//		$count = 0;
//		for($j=0;$j<$fleet_size;++$j) {
//			if($count < MAXIMUM_FLEET_SIZE - 1) {
//				$temp[] = $fleet[$j];
//			}
//			else {
//				unset($players[$fleet[$j]]);
//			}
//			++$count;
//		}
//		$fleet = $temp;
//	}
//
//	// Add the inital combatants to their respective fleets
//	$fleet[] = (int)SmrSession::$account_id;
//
//	// Shuffle for random firing order
//	shuffle($fleet);
//	
//	return $fleet;
//}
//function protected_rating($account_id,&$players,&$weapons) {
//	if (DEBUG) $PHP_OUTPUT.=('Checking Protected Rating<br />');
//	// Calculate their attack rating
//	$weapons_damage = 0;
//	foreach($players[$account_id][WEAPONS] as $weapon) {
//		// Ignore drones (Weapon id 0)
//		if($weapon) {
//			$weapons_damage += ($weapons[$weapon][1] + $weapons[$weapon][2]);
//		}
//
//	}
//	$maxDronesPercent = (35 + $players[$account_id][LEVEL] * .6 + ($players[$account_id][LEVEL] - 1) * .4 + 15) * .01;
//	$maxDrones = $maxDronesPercent * $players[$account_id][DRONES_ORIGINAL];
//	$rating = round((($weapons_damage + $maxDrones * 2) / 40));
//	// Aligment adjusts their permitted attack rating for protection
//	if($players[$account_id][ALIGNMENT] > 0) {
//		$alignment_mod = floor($players[$account_id][ALIGNMENT]/150);
//	}
//	else {
//		$alignment_mod = ceil($players[$account_id][ALIGNMENT]/150);
//	}
//
//	if($rating == 0 || ($rating <= (3 + $alignment_mod) && $rating <= 8)) {
//		return TRUE;
//	}
//
//	return FALSE;
//}
//function planetFires(&$attackers, &$planet, &$players)
//{
//	if (DEBUG) $PHP_OUTPUT.=('Planet Fires<br />');
//	global $db,$player;
//	//Turrets Fire
//	for ($i = 0; $i < $planet[TURRETS]; $i++)
//	{
//		//select target for this turret
//		$target = $attackers[array_rand($attackers)];
//		//get results
//		$result = planetFiresTurret($attackers, $planet, $players, $target);
//		$result[TARGET] = $target;
//		$players[$target][SHIELDS] -= $result[SHIELD_DMG_DONE];
//		$players[$target][DRONES] -= round($result[DRONE_DMG_DONE] / 3);
//		$players[$target][ARMOR] -= $result[ARMOR_DMG_DONE];
//		$planet[PLANET_RESULTS][] = $result;
//	}
//	//Drones Fire
//	if ($planet[PLANET_DRONES] > 0) {
//		$target = $attackers[array_rand($attackers)];
//		$result = planetFiresDrones($attackers, $planet, $players, $target);
//		$result[TARGET] = $target;
//		//update the player
//		$players[$target][SHIELDS] -= $result[SHIELD_DMG_DONE];
//		$players[$target][DRONES] -= round($result[DRONE_DMG_DONE] / 3);
//		$players[$target][ARMOR] -= $result[ARMOR_DMG_DONE];
//		$planet[PLANET_RESULTS][] = $result;
//	}
//}
//function planetFiresTurret($attackers, $planet, &$players, $target) {
//	if (DEBUG) $PHP_OUTPUT.=('Planet Fires its turret<br />');
//	$result = array(0,0,0,0,NORMAL_HIT);
//	$planetAccuracy = round(25 + ($planet[TURRETS] + $planet[GENERATORS] + $planet[HANGARS]) / 3);
//	if (mt_rand(1,100) <= $planetAccuracy) {
//		//player is hit
//		$damage = 250;
//		//check for shields
//		if ($players[$target][SHIELDS] > 0) {
//			if ($players[$target][SHIELDS] >= $damage)
//				$result[SHIELD_DMG_DONE] = $damage;
//			else
//				$result[SHIELD_DMG_DONE] = $players[$target][SHIELDS];
//			$result[RESULT_OF_WEAPON] = NORMAL_HIT;
//			return $result;
//		} elseif ($players[$target][DRONES] > 0) {
//			if ($players[$target][DRONES] * 3 >= $damage)
//				$result[DRONE_DMG_DONE] = $damage;
//			else
//				$result[DRONE_DMG_DONE] = $players[$target][DRONES] * 3;
//			$result[RESULT_OF_WEAPON] = NORMAL_HIT;
//			return $result;
//		} elseif ($players[$target][ARMOR] > 0) {
//			if ($players[$target][ARMOR] > $damage) {
//				$result[ARMOR_DMG_DONE] = $damage;
//				$result[RESULT_OF_WEAPON] = NORMAL_HIT;
//			} else {
//				$result[ARMOR_DMG_DONE] = $players[$target][ARMOR];
//				$result[RESULT_OF_WEAPON] = FINAL_HIT;
//				//mark them as dead
//				$players[$target][KILLER] = 1;
//			}
//			return $result;
//		} else {
//			$result[RESULT_OF_WEAPON] = ALREADY_DEAD;
//			return $result;
//		}
//	} else {
//		$result[RESULT_OF_WEAPON] = WEAPON_MISS;
//		return $result;
//	}
//}
//function planetFiresDrones($attackers, $planet, &$players, $target)
//{
//	if (DEBUG) $PHP_OUTPUT.=('Planet Fires drones<br />');
//	$result = array(0,0,0,0,NORMAL_HIT);
//	$result[DRONES_FIRED] = $planet[PLANET_DRONES];
//	$damage = $planet[PLANET_DRONES];
//	if($players[$target][DCS])
//		$damage = round($damage / (4 / 3));
//	//start with shields
//	if ($players[$target][SHIELDS] > 0)
//	{
//		if ($players[$target][SHIELDS] > $damage)
//			$result[SHIELD_DMG_DONE] = $damage;
//		else
//			$result[SHIELD_DMG_DONE] = $players[$target][SHIELDS];
//	}
//	//remove shield dmg done from the amount remaining
//	$damage -= $result[SHIELD_DMG_DONE];
//	if ($damage == 0) {
//		$result[RESULT_OF_WEAPON] = NORMAL_HIT;
//		return $result;
//	}
//	if ($players[$target][DRONES] > 0)
//	{
//		if ($players[$target][DRONES] * 3 > $damage)
//			$result[DRONE_DMG_DONE] = $damage;
//		else
//			$result[DRONE_DMG_DONE] = $players[$target][DRONES] * 3;
//	}
//	$damage -= $result[DRONE_DMG_DONE];
//	if ($damage == 0) {
//		$result[RESULT_OF_WEAPON] = NORMAL_HIT;
//		return $result;
//	}
//	if ($players[$target][ARMOR] > 0)
//	{
//		if ($players[$target][ARMOR] > $damage) {
//			$result[ARMOR_DMG_DONE] = $damage;
//			$result[RESULT_OF_WEAPON] = NORMAL_HIT;
//		} else {
//			$result[ARMOR_DMG_DONE] = $players[$target][ARMOR];
//			$result[RESULT_OF_WEAPON] = FINAL_HIT;
//			//mark them as dead
//			$players[$target][KILLER] = 1;
//		}
//		return $result;
//	}
//	$result[RESULT_OF_WEAPON] = ALREADY_DEAD;
//	return $result;
//}
//function fleetFires(&$attackers, &$planet, &$players, &$weapons)
//{
//	if (DEBUG) $PHP_OUTPUT.=('Fleet Fires<br />');
//	$fleet_size = count($attackers);
//	// Process each player in turn
//	for($i=0;$i<$fleet_size;++$i) {
//		playerFires($attackers[$i],$planet, $players,$weapons);
//	}
//}
//
//function playerFires($attacker, &$planet, &$players, &$weapons)
//{
//	if (DEBUG) $PHP_OUTPUT.=('Player fires<br />');
//	global $db,$player;
//	
//	$num_weapons = count($players[$attacker][WEAPONS]);
//	// Process each weapon in turn
//	for($i=0;$i<$num_weapons;++$i)
//	{
//		$result = playerFiresWeapon($players[$attacker][WEAPONS][$i],$attacker,$planet,$players,$weapons);
//		
//		// Take the appropriate damage from the planet
//		$planet[PLANET_SHIELDS] -= $result[SHIELD_DMG_DONE];
//		$planet[PLANET_DRONES] -= floor($result[DRONE_DMG_DONE] / 3);
//		$planet[PLANET_DRONES] -= floor($result[DRONES_HIT_BEHIND_SHIELDS] / 12);
//		
//		$players[$attacker][RESULTS][] = $result;
//	}
//	
//}
//
//function playerFiresWeapon($weapon,$attacker,&$planet,&$players,&$weapons)
//{
//	if (DEBUG) $PHP_OUTPUT.=('Player Fires Weap<br />');
//	$result = array(0,0,0,NORMAL_HIT,0);
//
//	// Does the weapon hit?
//	if($weapon) {
//		$hit = $weapons[$weapon][ACCURACY] + pow($players[$attacker][LEVEL],1.25) / 3.5 - $planet[PLANET_LEVEL] / 7 + $players[$attacker][LEVEL] * .5;
//
//		//linear random value since we take level differences into account in the above eqn.
//		$rand = mt_rand(1,100);
//
//		if($rand > $hit) {
//			$result[RESULT_OF_WEAPON] = WEAPON_MISS;
//			return $result;
//		}
//	}
//
//	// Drones are weapon id 0 and their damage rolls over
//	if(!$weapon) {
//		// Calculate how many drones actually fire
//
//		//$percent_attacking = (mt_rand(3, 53) + mt_rand($curr_attacker->level_id / 4, $curr_attacker->level_id)) / 100;
//		// Azool's New hotness
//		$value1 = pow($players[$attacker][LEVEL],1.85) / 65;
//		$value2 = (mt_rand(0,50) + mt_rand(0,50)) / 2;
//		$value3 = mt_rand($players[$attacker][LEVEL] / 4, $players[$attacker][LEVEL] * 1.25);
//		$drones_percentage = ($value1 + $value2 + $value3) / 100;
//
//		if($drones_percentage < 0) $drones_percentage = 0;
//		else if($drones_percentage > 1) $drones_percentage = 1;
//
//		$result[DRONES_FIRED] = ceil($players[$attacker][DRONES_ORIGINAL] * $drones_percentage);
//		$potential_damage = round(2 * $result[DRONES_FIRED] / 5);
//
//		// Yes, they can miss with all drones
//		if(!$potential_damage) {
//			$result[RESULT_OF_WEAPON] = WEAPON_MISS;
//			return $result;
//		}
//	}
//	else {
//		$potential_damage = $weapons[$weapon][SHIELD_DAMAGE];
//	}
//
//	// Check for planet being dead
//	if($planet[PLANET_SHIELDS] == 0 && $planet[PLANET_DRONES] == 0) {
//		$result[RESULT_OF_WEAPON] = PLANET_DEAD;
//		return $result;
//	}
//	
//	// Try to hit shields
//	if($planet[PLANET_SHIELDS] != 0 ) {
//		// Does the weapon do shield damage?
//		if($potential_damage) {
//			// Have we produced more damage than there are shields remaining?
//			if($potential_damage >= $planet[PLANET_SHIELDS]) {
//				$result[SHIELD_DMG_DONE] =  $planet[PLANET_SHIELDS];
//				if ($planet[PLANET_DRONES] == 0) {
//					$result[RESULT_OF_WEAPON] = PLANET_DEAD;
//					return $result;
//				}
//			}
//			else {
//				$result[SHIELD_DMG_DONE] = $potential_damage;
//			}
//
//			// If it's an ordinary weapon or drones are out of damage then return
//			if($weapon || $result[0] == $potential_damage) {
//				$result[RESULT_OF_WEAPON] = NORMAL_HIT;
//				return $result;
//			}
//		}
//		else {
//			if ($weapon && $weapons[$weapon][ARMOR_DAMAGE] && $planet[PLANET_DRONES]) {
//				if ($weapons[$weapon][ARMOR_DAMAGE] > $planet[PLANET_DRONES] * 12)
//					$result[DRONES_HIT_BEHIND_SHIELDS] = $planet[PLANET_DRONES] * 12;
//				else
//					$result[DRONES_HIT_BEHIND_SHIELDS] = $weapons[$weapon][ARMOR_DAMAGE];
//				$result[RESULT_OF_WEAPON] = NORMAL_HIT;
//				return $result;
//			}
//			$result[RESULT_OF_WEAPON] = ARMOR_ON_SHIELD;
//			return $result;
//		}
//	}
//
//	// If a drone shot then adjust damage so we work in units of 1 drone
//	if(!$weapon) {
//		$potential_damage -= $result[SHIELD_DMG_DONE];
//		$potential_damage = 2 * floor($potential_damage/2);
//
//		if($potential_damage == 0) {
//			$result[RESULT_OF_WEAPON] = NORMAL_HIT;
//			return $result;
//		}
//	}
//	else {
//		$potential_damage = $weapons[$weapon][ARMOR_DAMAGE];
//	}
//
//	// No shields left, try to hit their drones
//	if($planet[PLANET_DRONES] != 0 ) {
//		// Does the weapon do armor damage?
//		if($potential_damage) {
//			// Have we produced more damage than there are drones remaining?
//			if($potential_damage >= $planet[PLANET_DRONES] * 3) {
//				$result[DRONE_DMG_DONE] =  $planet[PLANET_DRONES] * 3;
//				if ($planet[PLANET_SHIELDS] == 0) {
//					$result[RESULT_OF_WEAPON] = PLANET_DEAD;
//					return $result;
//				}
//			}
//			else {
//				$result[DRONE_DMG_DONE] = $potential_damage;
//			}
//			// If it's an ordinary weapon or drones are out of damage then return
//			if($weapon || $result[DRONE_DMG_DONE] == $potential_damage) {
//				$result[RESULT_OF_WEAPON] = NORMAL_HIT;
//				return $result;
//			}
//		}
//		else {
//			$result[RESULT_OF_WEAPON] = SHIELD_ON_DRONES;
//			return $result;
//		}
//	}
//
//	// If a drone shot then adjust damage so we work in units of 1 drone
//	if(!$weapon) {
//		$potential_damage -= $result[1];
//		$potential_damage = 2 * floor($potential_damage/2);
//
//		if($potential_damage == 0) {
//			$result[RESULT_OF_WEAPON] = NORMAL_HIT;
//			return $result;
//		}
//	}
//	else {
//		$potential_damage = $weapons[$weapon][ARMOR_DAMAGE];
//	}
//	return $result;
//	
//}
//function processNews($fleet, $planet) {
//	if (DEBUG) $PHP_OUTPUT.=('News Process<br />');
//	global $db, $player;
//	$allowed = TIME - 600;
//	$db->getField('SELECT * FROM news WHERE type = \'breaking\' AND game_id = '.$player->getGameID().' AND time > '.$allowed);
//	if (!$db->getNumRows()) {
//		if (sizeof($fleet) >= 5) {
//			$db->query('SELECT  * FROM player WHERE game_id = '.$player->getGameID().' AND account_id = ' . $planet[OWNER]);
//			$db->nextRecord();
//			$text = sizeof($fleet) . ' members of '.$player->getAllianceName().' have been spotted attacking ' .
//					$planet[PLANET_NAME] . ' in sector ' . $player->getSectorID() . '
//					.  The planet is owned by ' . stripslashes($db->getField('player_name'));
//			if ($db->getField('alliance_id') > 0) {
//				$db->query('SELECT * FROM alliance WHERE alliance_id = ' . $db->getField('alliance_id') . ' AND game_id = '.$player->getGameID());
//				$db->nextRecord();
//				$text .= ', a member of ' . stripslashes($db->getField('alliance_name'));
//			}
//			$text .= '.';
//			$text = mysql_real_escape_string($text);
//			$db->query('INSERT INTO news (game_id, time, news_message, type) VALUES ('.$player->getGameID().', ' . TIME . ', '.$db->escapeString($text).', \'breaking\')');
//		}
//	}
//	
//}
//function hofTracker($players, $planet) {
//	if (DEBUG) $PHP_OUTPUT.=('Tracking HoF<br />');
//	global $db, $player;
//	//keep track of players who should get credit if the PB is successful.
//	$allowed = TIME - 60 * 60 * 3;
//	$db->query('DELETE FROM player_attacks_planet WHERE time < '.$allowed);
//	foreach ($players as $accId => $crap) {
//		$db->query('REPLACE INTO player_attacks_planet (game_id, account_id, sector_id, time, level) VALUES ' .
//					'('.$player->getGameID().', '.$accId.', '.$player->getSectorID().', ' . TIME . ', ' . round($planet[PLANET_LEVEL]) . ')');
//	}
//}
//function processResults($players, $planet, $fleet, $weapons) {
//	if (DEBUG) $PHP_OUTPUT.=('Processing Results<br />');
//	global $db, $player;
//	$results = array('','',0,0,array());
//	//planet is updated in downgrade function, all we need to do is format text
//	$planetDisplay = '<h2>Planet Results</h2>';
//	$totalPlanetDamage = 0;
//	foreach ($planet[PLANET_RESULTS] as $resultArray) {
//		$totalPlanetDamage += $resultArray[SHIELD_DMG_DONE] + $resultArray[DRONE_DMG_DONE] + $resultArray[ARMOR_DMG_DONE];
//		$planetDisplay .= '<span style="color:yellow;font-variant:small-caps">' . $planet[PLANET_NAME] . '</span>';
//		if ($resultArray[DRONES_FIRED]) $planetDisplay .= ' launches ' . $resultArray[DRONES_FIRED] . ' drones at ';
//		else $planetDisplay .= ' fires a turret at ';
//		if ($resultArray[RESULT_OF_WEAPON] == ALREADY_DEAD) $planetDisplay .= 'the debris that used to be ';
//		$planetDisplay .= $players[$resultArray[TARGET]][PLAYER_NAME];
//		if ($resultArray[RESULT_OF_WEAPON] == NORMAL_HIT || $resultArray[RESULT_OF_WEAPON] == FINAL_HIT) $planetDisplay .= ' destroying ';
//		elseif ($resultArray[RESULT_OF_WEAPON] == WEAPON_MISS) $planetDisplay .= ' and misses';
//		elseif ($resultArray[RESULT_OF_WEAPON] == ALREADY_DEAD) $planetDisplay .= '';
//		else $planetDisplay .= ' and I have no idea what the hell happens!  Please save this screen and notify Azool.';
//		if ($resultArray[SHIELD_DMG_DONE]) $planetDisplay .= '<span class="cyan">' . $resultArray[SHIELD_DMG_DONE] . '</span> shields';
//		if ($resultArray[DRONE_DMG_DONE]) {
//			if ($resultArray[SHIELD_DMG_DONE])
//				if ($resultArray[ARMOR_DMG_DONE]) $planetDisplay .= ', ';
//				else $planetDisplay .= ' and ';
//			$planetDisplay .= '<span class="yellow">';
//			$planetDisplay .= floor($resultArray[DRONE_DMG_DONE] / 3);
//			$planetDisplay .= '</span> drones';
//		} if ($resultArray[ARMOR_DMG_DONE]) {
//			if ($resultArray[DRONE_DMG_DONE] || $resultArray[SHIELD_DMG_DONE]) $planetDisplay .= ' and ';
//			$planetDisplay .= '<span class="red">' . $resultArray[ARMOR_DMG_DONE] . '</span> armor';
//		}
//		$planetDisplay .= '.<br />';
//		if ($resultArray[RESULT_OF_WEAPON] == FINAL_HIT) {
//			$planetDisplay .= '<span style="color:yellow;">' . $players[$resultArray[TARGET]][PLAYER_NAME] . '</span>';
//			$planetDisplay .= ' is <span style="color:red;">DESTROYED.</span><br />';
//		}
//		$results[DMG_TO_PLAYER][$resultArray[TARGET]] += $resultArray[SHIELD_DMG_DONE] + $resultArray[DRONE_DMG_DONE] + $resultArray[ARMOR_DMG_DONE];
//	}
//	$planetDisplay .= '<br /><span style="color:yellow;font-variant:small-caps">' . $planet[PLANET_NAME] . '</span>';
//	$planetDisplay .= ' does a total of <span class="red">$totalPlanetDamage</span> damage.<br />';
//	$results[PLANET_DISPLAY] = $planetDisplay;
//	$results[TOTAL_PLANET_DMG] = $totalPlanetDamage;
//	$playerDisplay = '<h2>Attacker Results</h2>';
//	$totalPlayerDamage = 0;
//	foreach ($fleet as $accId) {
//		$traderDisplay = '';
//		$weapon = 0;
//		$totalTraderDamage = 0;
//		//make sure this element exists to prevent blank messages
//		$results[DMG_TO_PLAYER][$accId] += 0;
//		foreach ($players[$accId][RESULTS] as $resultArray) {
//			$totalTraderDamage += $resultArray[SHIELD_DMG_DONE] + $resultArray[DRONE_DMG_DONE] + $resultArray[ARMOR_DMG_DONE] + (3*floor($resultArray[DRONES_HIT_BEHIND_SHIELDS] / 12));
//			$traderDisplay .= $players[$accId][PLAYER_NAME];
//			if(!$players[$accId][WEAPONS][$weapon])
//				if($resultArray[DRONES_FIRED]) $traderDisplay .= ' launches <span class="yellow">' . $resultArray[DRONES_FIRED] . '</span> drones';
//				else $traderDisplay .= ' fails to launch their drones';
//			else {
//				$traderDisplay .= ' fires their ';
//				$traderDisplay .= $weapons[$players[$accId][WEAPONS][$weapon]][WEAPON_NAME];
//			}
//			$traderDisplay .= ' at ';
//			if($resultArray[RESULT_OF_WEAPON] == PLANET_DEAD)
//				$traderDisplay .= ' the surface of ';
//			$traderDisplay .= '<span style="color:yellow;font-variant:small-caps">' . $planet[PLANET_NAME] . '</span>';
//			if($resultArray[RESULT_OF_WEAPON] == ARMOR_ON_SHIELD)
//				$traderDisplay .= ' which is deflected by its shields.';
//			else if ($resultArray[RESULT_OF_WEAPON] == SHIELD_ON_DRONES)
//				$traderDisplay .= ' which proves ineffective against its combat drones.';
//			else if ($resultArray[RESULT_OF_WEAPON] == WEAPON_MISS && $players[$accId][WEAPONS][$weapon])
//				$traderDisplay .= ' and misses every critical system.';
//			else if($resultArray[RESULT_OF_WEAPON] == PLANET_DEAD)
//				$traderDisplay .= '.';
//			else {
//				$traderDisplay .= ' destroying ';
//				if($resultArray[SHIELD_DMG_DONE])
//					$traderDisplay .= '<span class="cyan">' . $resultArray[SHIELD_DMG_DONE] . '</span> shields';
//				if($resultArray[DRONE_DMG_DONE] || $resultArray[DRONES_HIT_BEHIND_SHIELDS]) {
//					if($resultArray[SHIELD_DMG_DONE])
//						$traderDisplay .= ' and ';
//					$traderDisplay .= '<span class="yellow">';
//					if ($resultArray[DRONE_DMG_DONE]) $traderDisplay .= floor($resultArray[DRONE_DMG_DONE] / 3);
//					else $traderDisplay .= floor($resultArray[DRONES_HIT_BEHIND_SHIELDS] / 12);
//					$traderDisplay .= '</span> drones';
//				}
//				$traderDisplay .= '.';
//			}
//			$traderDisplay .= '<br />';
//			if ($resultArray[RESULT_OF_WEAPON] == PLANET_DEAD) {
//				$traderDisplay .= '<span style="color:yellow;font-variant:small-caps">' . $planet[PLANET_NAME] . '\'s</span> defenses are ';
//				$traderDisplay .= '<span style="color:red;">DESTROYED.</span><br />';
//			}
//			$weapon++;
//		}
//		$totalPlayerDamage += $totalTraderDamage;
//		$db->query('UPDATE account_has_stats SET planet_damage = planet_damage + '.$totalTraderDamage.' WHERE account_id = '.$accId);
//		$db->query('UPDATE player_has_stats SET planet_damage = planet_damage + '.$totalTraderDamage.' WHERE account_id = '.$accId.' AND game_id = '.$player->getGameID());
//		$players[$accId][EXPERIENCE_GAINED] = round($totalTraderDamage * .25);
//		$traderDisplay .= '<br />' . $players[$accId][PLAYER_NAME] . ' does a total of <span class="red">'.$totalTraderDamage.'</span> damage.<br />';
//		//append this display to the overal display.
//		$playerDisplay .= $traderDisplay . '<br />';
//		$traderDisplay = '<span style="color:yellow;font-variant:small-caps">' . $planet[PLANET_NAME] . '</span>\'s defenses ' . 
//							'do a total of <span class="red">'.$totalPlanetDamage.'</span> damage. <span class="red">' . 
//							$results[DMG_TO_PLAYER][$accId] . '</span> of which hit you.<br /><br />' . $traderDisplay . 
//							'You have gained <span class="blue">' . round($totalTraderDamage * .25) . '</span> experience.';
//		if ($accId != $player->getAccountID())
//			$db->query('REPLACE INTO sector_message (account_id, game_id, message) VALUES ('.$accId.', '.$player->getGameID().', ' . $db->escape_string($traderDisplay) . ')');
//	}
//	$playerDisplay .= 'This team does a total of <span class="red">'.$totalPlayerDamage.'</span> damage.<br />';
//	$results[PLAYER_DISPLAY] = $playerDisplay;
//	$results[TOTAL_PLAYER_DMG] = $totalPlayerDamage;
//	//we need to update the database for the players
//	$temp = array();
//	$ships = array();
//	$hqs = array();
//	foreach ($players as $accId => $playerArray) {
//		if ($playerArray[SHIELDS] == 0 && $playerArray[ARMOR] == 0) {
//			$temp[] = $accId;			
//			$ships[] = $players[$accId][SHIP_ID];
//			$hqs[] = $players[$accId][RACE_ID];
//		} else {
//			$db->query('UPDATE ship_has_hardware SET amount=' . $players[$accId][SHIELDS] . ' WHERE hardware_type_id=1 AND account_id=' . $accId . ' AND game_id=' . $player->getGameID() . ' LIMIT 1');
//			$db->query('UPDATE ship_has_hardware SET amount=' . $players[$accId][ARMOR] . ' WHERE hardware_type_id=2 AND account_id=' . $accId . ' AND game_id=' . $player->getGameID() . ' LIMIT 1');
//			$db->query('UPDATE ship_has_hardware SET amount=' . $players[$accId][DRONES] . ' WHERE hardware_type_id=4 AND account_id=' . $accId . ' AND game_id=' . $player->getGameID() . ' LIMIT 1');
//			$db->query('UPDATE player SET experience = experience + ' . $players[$accId][EXPERIENCE_GAINED] . ' WHERE account_id = '.$accId.' AND game_id = '.$player->getGameID());
//		}
//	}
//	podPlayers($temp, $ships, $hqs, $planet, $players);
//	unset($temp);
//	return $results;
//}
//}
//function sendReport($results, $planet) {
//	if (DEBUG) $PHP_OUTPUT.=('Sending Reports<br />');
//	global $player, $db;
//	$db->query('SELECT * FROM player WHERE account_id = ' . $planet[OWNER] . ' AND game_id = '.$player->getGameID().' LIMIT 1');
//	$db->nextRecord();
//	$ownerAlliance = $db->getField('alliance_id');
//	$db->query('SELECT * FROM planet WHERE sector_id = '.$player->getSectorID().' AND game_id = '.$player->getGameID());
//	$db->nextRecord();
//	$planetName = '<span style="color:yellow;font-variant:small-caps">' . stripslashes($db->getField('planet_name')) . '</span>';
//	$mainText = 'From the reports we have been able to gather the following information:<br /><br />';
//	$mainText .= $results[PLANET_DISPLAY] . '<br />' . $results[PLAYER_DISPLAY];
//	if ($ownerAlliance > 0) {
//		$topic = 'Planet Attack Report Sector '.$player->getSectorID();
//		$text = 'Reports from the surface of '.$planetName.' confirm that it is under <span class="red">attack</span>!<br />';
//		$text .= $mainText;
//		$text = mysql_real_escape_string($text);
//		$thread_id = 0;
//		$db->query('SELECT * FROM alliance_thread_topic WHERE game_id = '.$player->getGameID().' AND alliance_id = '.$ownerAlliance.' AND topic = '.$db->escapeString($topic).' LIMIT 1');
//		if ($db->nextRecord()) $thread_id = $db->getField('thread_id');
//		if ($thread_id == 0)
//		{
//			$db->query('SELECT * FROM alliance_thread_topic WHERE game_id = '.$player->getGameID().' AND alliance_id = '.$ownerAlliance.' ORDER BY thread_id DESC LIMIT 1');
//			if ($db->nextRecord())
//				$thread_id = $db->getField('thread_id') + 1;
//			else $thread_id = 1;
//			$db->query('INSERT INTO alliance_thread_topic (game_id, alliance_id, thread_id, topic) VALUES ' .
//						'('.$player->getGameID().', '.$ownerAlliance.', '.$thread_id.', '.$db->escapeString($topic).')');
//		}
//		$db->query('SELECT * FROM alliance_thread WHERE alliance_id = '.$ownerAlliance.' AND game_id = '.$player->getGameID().' AND ' .
//					'thread_id = '.$thread_id.' ORDER BY reply_id DESC LIMIT 1');
//		if ($db->nextRecord()) $reply_id = $db->getField('reply_id') + 1;
//		else $reply_id = 1;
//		$db->query('INSERT INTO alliance_thread (game_id, alliance_id, thread_id, reply_id, text, sender_id, time) VALUES ' .
//				'('.$player->getGameID().', '.$ownerAlliance.', '.$thread_id.', '.$reply_id.', '.$db->escapeString($text).', 0, ' . TIME . ')');
//		$db->query('SELECT * FROM player WHERE alliance_id = '.$ownerAlliance.' AND game_id = '.$player->getGameID());
//		while ($db->nextRecord())
//			$temp[] = $db->getField('account_id');
//		foreach ($temp as $tempAccId) {
//			$db->query('INSERT INTO player_has_unread_messages (account_id, game_id, message_type_id) VALUES ' .
//						'('.$tempAccId.', '.$player->getGameID().', 3)');
//		}
//	} else {
//		$text = 'Reports from the surface of '.$planetName.' confirm that it is under <span class="red">attack</span>!<br />';
//		$text .= $mainText;
//		$text = mysql_real_escape_string($text);
//		$db->query('INSERT INTO message (game_id, account_id, message_type_id, message_text, sender_id, send_time) VALUES ' .
//					'('.$player->getGameID().', ' . $planet[OWNER] . ', 3, '.$db->escapeString($text).', 0, ' . TIME . ')');
//		$db->query('INSERT INTO player_has_unread_messages (account_id, game_id, message_type_id) VALUES ' .
//						'(' . $planet[OWNER] . ', '.$player->getGameID().', 3)');
//	} if ($player->getAllianceID() > 0) {
//		$topic = 'Planet Siege Report Sector '.$player->getSectorID();
//		$text = 'Reports have come in from the space above '.$planetName.' and have confirmed our <span class="red">siege</span>!<br />';
//		$text .= $mainText;
//		$text = mysql_real_escape_string($text);
//		$thread_id = 0;
//		$db->query('SELECT * FROM alliance_thread_topic WHERE game_id = '.$player->getGameID().' AND alliance_id = '.$player->getAllianceID().' AND topic = '.$db->escapeString($topic).' LIMIT 1');
//		if ($db->nextRecord()) $thread_id = $db->getField('thread_id');
//		if ($thread_id == 0)
//		{
//			$db->query('SELECT * FROM alliance_thread_topic WHERE game_id = '.$player->getGameID().' AND alliance_id = '.$player->getAllianceID().' ORDER BY thread_id DESC LIMIT 1');
//			if ($db->nextRecord())
//				$thread_id = $db->getField('thread_id') + 1;
//			else $thread_id = 1;
//			$db->query('INSERT INTO alliance_thread_topic (game_id, alliance_id, thread_id, topic) VALUES ' .
//						'('.$player->getGameID().', '.$player->getAllianceID().', '.$thread_id.', '.$db->escapeString($topic).')');
//		}
//		$db->query('SELECT * FROM alliance_thread WHERE alliance_id = '.$player->getAllianceID().' AND game_id = '.$player->getGameID().' AND ' .
//					'thread_id = '.$thread_id.' ORDER BY reply_id DESC LIMIT 1');
//		if ($db->nextRecord()) $reply_id = $db->getField('reply_id') + 1;
//		else $reply_id = 1;
//		$db->query('INSERT INTO alliance_thread (game_id, alliance_id, thread_id, reply_id, text, sender_id, time) VALUES ' .
//				'('.$player->getGameID().', '.$player->getAllianceID().', '.$thread_id.', '.$reply_id.', '.$db->escapeString($text).', 0, ' . TIME . ')');
//	}
//}

//function doLog($results) {
//	if (DEBUG) $PHP_OUTPUT.=('Logging<br />');
//	global $account, $player;
//	$account->log(12, 'Player attacks planet their team does ' . $results[TOTAL_PLAYER_DMG], $player->getSectorID());
//}
//function checkContinue($players, $planet) {
//	if (DEBUG) $PHP_OUTPUT.=('Continue?<br />');
//	global $player;
//	if ($players[$player->getAccountID()][KILLER]) return FALSE;
//	if ($planet[PLANET_SHIELDS] == 0 && $planet[PLANET_DRONES] == 0) return FALSE;
//	return TRUE;
//}
//if (DEBUG) $PHP_OUTPUT.=('Opening<br />');
//$planet = getPlanetArray();
//if (DEBUG) $PHP_OUTPUT.=('Level is still set to ' . $planet[PLANET_LEVEL] . '<br />');
////$players contains all player info for the trigger and his alliance IS
//$players = getPlayerArray();
////$weapons contains info for weapons being used this battle
//$weapons = getWeapons(getHardware($players));
////in case there are more than 10 players IS.  $fleet contains account_ids of the attackers
//$fleet = getFleet($players, $weapons);
//if (DEBUG) $PHP_OUTPUT.=('Pre news<br />');
//processNews($fleet, $planet);
//hofTracker($players, $planet);
//// Take off the 3 turns for attacking
//$player->takeTurns(3,1);
//$player->update();
//// fire shots
//if (DEBUG) $PHP_OUTPUT.=('Pre Shots<br />');
//planetFires($fleet,$planet,$players);
//fleetFires($fleet,$planet,$players,$weapons);
////get results in a way we want them
//$results = processResults($players, $planet, $fleet, $weapons);
////post on alliances MB or send to player
//planetDowngrade($results, $planet);
//sendReport($results, $planet);
////log player
//doLog($results);
////insert into combat logs
//$db->query('SELECT alliance_id FROM player WHERE account_id = ' . $planet[OWNER] . ' AND game_id = '.$player->getGameID().' LIMIT 1');
//$db->nextRecord();
//$ownerAlliance = $db->getField('alliance_id');
//$finalResults = $results[0] . '<br /><img src="images/planetAttack.jpg" alt="Planet Attack" title="Planet Attack"><br />' . $results[1];
//$db->query('INSERT INTO combat_logs VALUES(\'\',' . SmrSession::$game_id . ',\'PLANET\',' . $player->getSectorID() . ',' . TIME . ',' . SmrSession::$account_id . ',' . $player->getAllianceID() . ',' . $planet[OWNER] . ',' . $ownerAlliance . ',' . $db->escape_string(gzcompress($finalResults)) . ', \'FALSE\')');
//if (DEBUG) $PHP_OUTPUT.=('Pre Forward/Display<br />');
//$container=array();
//$container['url'] = 'skeleton.php';
//$container['body'] = 'planet_attack.php';
//$container['results'] = $results;
//if ($players[$player->getAccountID()][KILLER]) $container['override_death'] = TRUE;
//$container['continue'] = checkContinue($players, $planet);
//forward($container);

?>