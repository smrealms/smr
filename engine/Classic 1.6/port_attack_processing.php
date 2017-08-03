<?php


//general info
define ('MAX_TURNS', (Globals::getGameSpeed($player->getGameID()) * 400));
$limit = 10;
define ('MAXIMUM_FLEET_SIZE',$limit);

//result info
define('SHIELD_DMG_DONE', 0);
define('DRONE_DMG_DONE', 1);
define('ARMOUR_DMG_DONE', 2);
define('DRONES_FIRED', 3);
define('RESULT_OF_WEAPON', 4);
define('TARGET',5);
define('DRONES_HIT_BEHIND_SHIELDS',6);

//result[4]
define('NORMAL_HIT', 0);
define('SHIELD_ON_DRONES',1);
define('ARMOUR_ON_SHIELD',2);
define('PORT_DEAD',3);
define('FINAL_HIT',4);
define('WEAPON_MISS',5);
define('ALREADY_DEAD',6);
define('SHIELD_ON_ARMOUR',7);

//player array info
define('PLAYER_ID', 0);
define('PLAYER_NAME', 1);
define('ALLIANCE_ID',2);
define('RACE_ID', 3);
define('CREDITS', 4);
define('TURNS', 5);
define('ALIGNMENT', 6);
define('SHIP_ID', 7);
define('EXPERIENCE', 8);
define('LEVEL', 9);
define('SHIELDS', 10);
define('ARMOUR', 11);
define('DRONES', 12);
define('DRONES_ORIGINAL', 13);
define('DCS', 14);
define('WEAPONS', 15);
define('RESULTS', 16);
define('EXPERIENCE_GAINED', 17);
define('KILLER', 18); 
define('TOTAL_DAMAGE',19);
define('PERSONAL_DISPLAY', 20);

//weapon array info
define('WEAPON_NAME', 0);
define('SHIELD_DAMAGE', 1);
define('ARMOUR_DAMAGE', 2);
define('ACCURACY', 3);

//port array info
define('PORT_SHIELDS', 0);
define('PORT_DRONES', 1);
define('PORT_ARMOUR', 2);
define('STARTED', 3);
define('PORT_LEVEL',4);
define('PORT_RESULTS',5);
define('REFRESH',6);
define('PORT_RACE_ID',7);
define('FED_ARRIVAL',8);
define('PORT_CREDITS',9);
define('NUM_TURRETS',15);

//more general
define('MESSAGE_EXPIRES', TIME + 259200);

//final results info
define('PORT_DISPLAY',0);
define('PLAYER_DISPLAY',1);
define('TOTAL_PLAYER_DMG',2);
define('TOTAL_PORT_DMG',3);
define('DMG_TO_PLAYER',4);
define('DEBUG',0);
function getPortArray() {
	if (DEBUG) $PHP_OUTPUT.=('Entered port array<br>');
	global $db, $player;
	$db->query('SELECT  * FROM port WHERE sector_id = ' . $player->getSectorID() . ' AND game_id = ' . $player->getGameID());
	if ($db->next_record()) {
		$started = $db->f('attack_started');
		$refresh = $db->f('reinforce_time');
		$level = $db->f('level');
		if ($refresh < TIME) {
			//add news message
			processNews();
			//defences restock (check for fed arrival)
			$minsToStay = 30;
			if ($refresh + $minsToStay * 60 > TIME)
				$federal_mod = (TIME - $refresh - $minsToStay * 60) / (-6 * $minsToStay);
			else $federal_mod = 0;
			if ($federal_mod < 0) $federal_mod = 0;
			$rich_mod = floor( $db->f('credits') * 1e-7 );
			if($rich_mod < 0) $rich_mod = 0;
			$shields = round(($level * 1000 + 1000) + ($rich_mod * 500) + ($federal_mod * 500));
			$armour = round(($level * 1000 + 1000) + ($rich_mod * 500) + ($federal_mod * 500));
			$drones = round(($level * 100 + 100) + ($rich_mod * 50) + ($federal_mod * 50));
			$refresh = TIME + ($level * 5 * 60);
			$started = TIME;
		} else {
			$shields = $db->f('shields');
			$armour = $db->f('armour');
			$drones = $db->f('combat_drones');
		}
		$port = array($shields,$drones,$armour,$started,$level,array(),$refresh,$db->f('race_id'));
		$port[PORT_CREDITS] = $db->f('credits');
	} else {
		create_error('Port does not exist');
	}
	if ($port[PORT_ARMOUR] == 0 && $port[PORT_SHIELDS] == 0) {
		$container=create_container('skeleton.php','port_attack.php');
		$container['continue'] = FALSE;
		forward($container);
	}
	return $port;
}
function getPlayerArray() {
	if (DEBUG) $PHP_OUTPUT.=('Entered player array<br>');
	global $db, $session, $player, $account;
	//insert our trigger into the players array
	$query = 'SELECT * FROM player WHERE account_id = ' . $player->getAccountID() . ' AND game_id = ' . $player->getGameID();
	$db->query($query);
	$db->next_record();
	$players[$db->f('account_id')] = array(
		(int)$db->f('player_id'),
		get_colored_text($db->f('alignment'), stripslashes($db->f('player_name')) . ' (' . $db->f('player_id') . ')'),
		(int)$db->f('alliance_id'),
		(int)$db->f('race_id'),
		(int)$db->f('credits'),
		(int)$db->f('turns'),
		(int)$db->f('alignment'),
		(int)$db->f('ship_type_id'),
		(int)$db->f('experience'),
		0,0,0,0,0,false,array(),array(),0,0,0
	);
	
	//get treaty info
	$helperAlliances = array($player->getAllianceID());
	$db->query('SELECT * FROM alliance_treaties 
				WHERE game_id = '.$player->getGameID().'
				AND (alliance_id_1 = '.$player->getAllianceID().' OR alliance_id_2 = '.$player->getAllianceID().')
				AND raid_assist = 1 AND official = \'TRUE\'');
	while ($db->next_record()) {
		if ($db->f('alliance_id_1') == $player->getAllianceID()) $helperAlliances[] = $db->f('alliance_id_2');
		else $helperAlliances[] = $db->f('alliance_id_1');
	}
	if($player->getAllianceID()) {
		$query = '
		SELECT 
		player.account_id as account_id,
		player.player_id as player_id,
		player.player_name as player_name,
		player.race_id as race_id,
		player.alignment as alignment,
		player.ship_type_id as ship_type_id,
		player.experience as experience,
		player.credits as credits,
		player.turns as turns,
		player.alliance_id as alliance_id
		FROM player WHERE player.sector_id=' . $player->getSectorID() . '
			AND player.account_id!=' . SmrSession::$account_id . '
			AND player.game_id=' . SmrSession::$game_id . ' 
			AND player.land_on_planet=\'FALSE\' 
			AND player.newbie_turns=0
			AND player.last_active>' .  (TIME - 259200);

		$query .= ' AND player.alliance_id IN (' . implode(',',$helperAlliances) . ')';

		$db->query($query);

		while($db->next_record()) {
			$players[$db->f('account_id')] = array(
				(int)$db->f('player_id'),
				get_colored_text($db->f('alignment'),stripslashes($db->f('player_name')) . ' (' . $db->f('player_id') . ')'),
				(int)$db->f('alliance_id'),
				(int)$db->f('race_id'),
				(int)$db->f('credits'),
				(int)$db->f('turns'),
				(int)$db->f('alignment'),
				(int)$db->f('ship_type_id'),
				(int)$db->f('experience'),
				0,0,0,0,0,false,array(),array(),0,0,0
			);
		}
	}
	// Figure out everyone's level
	$db->query('SELECT level_id,requirement FROM level ORDER BY requirement DESC');
	while($db->next_record()) {
		$levels[$db->f('level_id')] = $db->f('requirement');
	}
	
	$num_players = count($players);
	$player_ids = array_keys($players);
	$num_levels = count($levels);
	$level_ids = array_keys($levels);
	
	for($i=0;$i<$num_players;++$i) {
		for($j=0;$j<$num_levels;++$j) {
			if($levels[$level_ids[$j]] <= $players[$player_ids[$i]][EXPERIENCE]) {
				$players[$player_ids[$i]][LEVEL] = $level_ids[$j];
				break;
			}
		}
	}
	
	// Everyone involved gets decloaked if they fire or not
	$db->query('DELETE FROM ship_is_cloaked WHERE account_id IN (' . implode(',',$player_ids) . ') AND game_id=' . SmrSession::$game_id);
	
	return $players;
	
}
function build_ships($ship_ids) {
	if (DEBUG) $PHP_OUTPUT.=('Build Ships<br>');
	global $db;
	$db->query('SELECT ship_type_id,cost,speed FROM ship_type WHERE ship_type_id IN (' . implode(',',$ship_ids) . ') LIMIT ' . count($ship_ids));
	while($db->next_record()) {
		$ships[$db->f('ship_type_id')] = array($db->f('cost'),$db->f('speed'));
	}
	return $ships;
}
function build_hqs(&$races) {
	if (DEBUG) $PHP_OUTPUT.=('Build HQ<br>');
	global $db,$session;
	// We know that race HQs have a location id of 101 + race_id
	$temp = array();
	foreach($races as $race_id) {
		$temp[] = $race_id + 101; 
	}
	$db->query('SELECT location_type_id,sector_id FROM location WHERE location_type_id IN (' . implode($temp,',') . ') AND game_id=' . SmrSession::$game_id . ' LIMIT ' . count($temp));
	while($db->next_record()) {
		$hqs[$db->f('location_type_id') - 101] = $db->f('sector_id');
	}
	return $hqs;
}
function getHardware(&$players) {
	if (DEBUG) $PHP_OUTPUT.=('Get Hardware<br>');
	global $db, $session, $player, $account;
	$players_in = implode(',',array_keys($players));
	
	$db->query('SELECT account_id,weapon_type_id FROM ship_has_weapon WHERE account_id IN (' . $players_in . ') AND game_id=' . SmrSession::$game_id . ' ORDER BY order_id ASC');
	$weapons = array();
	while($db->next_record()) {
		$weapons[] = $db->f('weapon_type_id');
		$players[$db->f('account_id')][WEAPONS][] = (int)$db->f('weapon_type_id');
	}
	
	$db->query('SELECT hardware_type_id,account_id,amount FROM ship_has_hardware WHERE account_id IN (' . $players_in . ') AND (hardware_type_id=' . HARDWARE_SHIELDS . ' OR hardware_type_id=' . HARDWARE_ARMOUR . ' OR hardware_type_id=' . HARDWARE_COMBAT . ' OR hardware_type_id=' . HARDWARE_DCS . ') AND game_id=' . SmrSession::$game_id);
	
	while($db->next_record()) {
		switch($db->f('hardware_type_id')) {
			case(HARDWARE_SHIELDS):
				$players[$db->f('account_id')][SHIELDS] = (int)$db->f('amount');
				break;
			case(HARDWARE_ARMOUR):
				$players[$db->f('account_id')][ARMOUR] = (int)$db->f('amount');
				break;
			case(HARDWARE_COMBAT):
				$players[$db->f('account_id')][DRONES] = (int)$db->f('amount');
				// They fire the same amount of drones they start the round with
				$players[$db->f('account_id')][DRONES_ORIGINAL] = (int)$db->f('amount');
				// Drones count as a weapon. It's important they are last in the order
				if($db->f('amount')) {
					$players[$db->f('account_id')][WEAPONS][] = 0;
				}
				break;
			case(HARDWARE_DCS):
				if($db->f('amount')) {
					$players[$db->f('account_id')][DCS] = TRUE;
				}
				break;
		}
	}
	return array_unique($weapons);
}
function getWeapons($weapon_ids) {
	if (DEBUG) $PHP_OUTPUT.=('Get Weps<br>');
	global $db,$session;
	$weapons = array();
	if (!sizeof($weapon_ids)) return $weapons;
	$db->query('SELECT weapon_type_id,weapon_name,shield_damage,armour_damage,accuracy FROM weapon_type WHERE weapon_type_id IN (' . implode(',',$weapon_ids) . ') LIMIT ' . count($weapon_ids));
	
	while($db->next_record()) {
		$weapons[$db->f('weapon_type_id')] = array(
												$db->f('weapon_name'),
												(int)$db->f('shield_damage'),
												(int)$db->f('armour_damage'),
												(int)$db->f('accuracy')
												);
	}	
	return $weapons;
}
function getFleet(&$players,&$weapons) {
	if (DEBUG) $PHP_OUTPUT.=('Get Fleet<br>');
	global $db,$session,$player;
	
	$player_ids = array_keys($players);
	$fleet = array();
		
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
			// Player and their target must not have dropped into fed protection
			if($account_id == $player->getAccountID()) {
				create_error('You are under federal protection');
			}
			unset ($players[$account_id]);
		}
		else if($account_id != $player->getAccountID()) {
			// We add the player and target to the fleet after capping
			if($players[$account_id][ALLIANCE_ID] == $player->getAllianceID()) {
				$fleet[] = $account_id;
			}
			else {
				$fleet[] = $account_id;
			}				
		}
	}

	// Cap fleet to the required size
	$fleet_size = count($fleet);
	if($fleet_size > (MAXIMUM_FLEET_SIZE - 1)) {
		// We shuffle to stop the same people being capped all the time
		shuffle($fleet);
		$temp = array();
		$count = 0;
		for($j=0;$j<$fleet_size;++$j) {
			if($count < MAXIMUM_FLEET_SIZE - 1) {
				$temp[] = $fleet[$j];
			}
			else {
				unset($players[$fleet[$j]]);
			}
			++$count;
		}
		$fleet = $temp;
	}

	// Add the inital combatants to their respective fleets
	$fleet[] = (int)SmrSession::$account_id;

	// Shuffle for random firing order
	shuffle($fleet);
	
	return $fleet;
}
function protected_rating($account_id,&$players,&$weapons) {
	if (DEBUG) $PHP_OUTPUT.=('Checking Protected Rating<br>');
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
function portFires(&$attackers, &$port, &$players) {
	if (DEBUG) $PHP_OUTPUT.=('Port Fires<br>');
	global $db,$session,$player;
	//Turrets Fire
	for ($i = 0; $i < NUM_TURRETS; $i++) {
		//select target for this turret
		$target = $attackers[array_rand($attackers)];
		//get results
		$result = portFiresTurret($attackers, $port, $players, $target);
		$result[TARGET] = $target;
		$players[$target][SHIELDS] -= $result[SHIELD_DMG_DONE];
		$players[$target][DRONES] -= round($result[DRONE_DMG_DONE] / 3);
		$players[$target][ARMOUR] -= $result[ARMOUR_DMG_DONE];
		$port[PORT_RESULTS][] = $result;
	}
	//Drones Fire
	if ($port[PORT_DRONES] > 0) {
		$target = $attackers[array_rand($attackers)];
		$result = portFiresDrones($attackers, $port, $players, $target);
		$result[TARGET] = $target;
		//update the player
		$players[$target][SHIELDS] -= $result[SHIELD_DMG_DONE];
		$players[$target][DRONES] -= round($result[DRONE_DMG_DONE] / 3);
		$players[$target][ARMOUR] -= $result[ARMOUR_DMG_DONE];
		$port[PORT_RESULTS][] = $result;
	}
}

function portFiresTurret($attackers, $port, &$players, $target) {
	if (DEBUG) $PHP_OUTPUT.=('Port Fires its turret<br>');
	$result = array(0,0,0,0,NORMAL_HIT);
	$portAccuracy = ($port[PORT_LEVEL] * 10) - ($players[$target][LEVEL] / 2);
	if ($portAccuracy < 0)
		$portAccuracy = 0;
	if (mt_rand(1,100) <= $portAccuracy) {
		//player is hit
		$damage = 250;
		//check for shields
		if ($players[$target][SHIELDS] > 0) {
			if ($players[$target][SHIELDS] >= $damage)
				$result[SHIELD_DMG_DONE] = $damage;
			else
				$result[SHIELD_DMG_DONE] = $players[$target][SHIELDS];
			$result[RESULT_OF_WEAPON] = NORMAL_HIT;
			return $result;
		} elseif ($players[$target][DRONES] > 0) {
			if ($players[$target][DRONES] * 3 >= $damage)
				$result[DRONE_DMG_DONE] = $damage;
			else
				$result[DRONE_DMG_DONE] = $players[$target][DRONES] * 3;
			$result[RESULT_OF_WEAPON] = NORMAL_HIT;
			return $result;
		} elseif ($players[$target][ARMOUR] > 0) {
			if ($players[$target][ARMOUR] > $damage) {
				$result[ARMOUR_DMG_DONE] = $damage;
				$result[RESULT_OF_WEAPON] = NORMAL_HIT;
			} else {
				$result[ARMOUR_DMG_DONE] = $players[$target][ARMOUR];
				$result[RESULT_OF_WEAPON] = FINAL_HIT;
				//mark them as dead
				$players[$target][KILLER] = 1;
			}
			return $result;
		} else {
			$result[RESULT_OF_WEAPON] = ALREADY_DEAD;
			return $result;
		}
	} else {
		$result[RESULT_OF_WEAPON] = WEAPON_MISS;
		return $result;
	}
}

function portFiresDrones($attackers, $port, &$players, $target) {
	if (DEBUG) $PHP_OUTPUT.=('Port Fires drones<br>');
	$result = array(0,0,0,0,NORMAL_HIT);
	$result[DRONES_FIRED] = $port[PORT_DRONES];
	$damage = $port[PORT_DRONES];
	if($players[$target][DCS])
		$damage = round($damage / (4 / 3));
	//start with shields
	if ($players[$target][SHIELDS] > 0) {
		if ($players[$target][SHIELDS] > $damage)
			$result[SHIELD_DMG_DONE] = $damage;
		else
			$result[SHIELD_DMG_DONE] = $players[$target][SHIELDS];
	}
	//remove shield dmg done from the amount remaining
	$damage -= $result[SHIELD_DMG_DONE];
	if ($damage == 0) {
		$result[RESULT_OF_WEAPON] = NORMAL_HIT;
		return $result;
	}
	if ($players[$target][DRONES] > 0) {
		if ($players[$target][DRONES] * 3 > $damage)
			$result[DRONE_DMG_DONE] = $damage;
		else
			$result[DRONE_DMG_DONE] = $players[$target][DRONES] * 3;
	}
	$damage -= $result[DRONE_DMG_DONE];
	if ($damage == 0) {
		$result[RESULT_OF_WEAPON] = NORMAL_HIT;
		return $result;
	}
	if ($players[$target][ARMOUR] > 0) {
		if ($players[$target][ARMOUR] > $damage) {
			$result[ARMOUR_DMG_DONE] = $damage;
			$result[RESULT_OF_WEAPON] = NORMAL_HIT;
		} else {
			$result[ARMOUR_DMG_DONE] = $players[$target][ARMOUR];
			$result[RESULT_OF_WEAPON] = FINAL_HIT;
			//mark them as dead
			$players[$target][KILLER] = 1;
		}
		return $result;
	}
	$result[RESULT_OF_WEAPON] = ALREADY_DEAD;
	return $result;
}
function fleetFires(&$attackers, &$port, &$players, &$weapons) {
	if (DEBUG) $PHP_OUTPUT.=('Fleet Fires<br>');
	$fleet_size = count($attackers);
	// Process each player in turn
	for($i=0;$i<$fleet_size;++$i) {
		playerFires($attackers[$i],$port, $players,$weapons);
	}
}
function playerFires($attacker, &$port, &$players, &$weapons) {
	if (DEBUG) $PHP_OUTPUT.=('Player fires<br>');
	global $db,$session,$player;
	
	$num_weapons = count($players[$attacker][WEAPONS]);
	// Process each weapon in turn
	for($i=0;$i<$num_weapons;++$i) {
		$result = playerFiresWeapon($players[$attacker][WEAPONS][$i],$attacker,$port,$players,$weapons);
		
		// Take the appropriate damage from the port
		$port[PORT_SHIELDS] -= $result[SHIELD_DMG_DONE];
		$port[PORT_DRONES] -= floor($result[DRONE_DMG_DONE] / 3);
		$port[PORT_DRONES] -= floor($result[DRONES_HIT_BEHIND_SHIELDS] / 60);
		$port[PORT_ARMOUR] -= $result[ARMOUR_DMG_DONE];
		
		$players[$attacker][RESULTS][] = $result;
	}
	
}
function playerFiresWeapon($weapon,$attacker,&$port,&$players,&$weapons) {
	if (DEBUG) $PHP_OUTPUT.=('Player Fires Weap<br>');
	$result = array(0,0,0,NORMAL_HIT,0);

	// Does the weapon hit?
	if($weapon) {
		$hit = $weapons[$weapon][ACCURACY] + pow($players[$attacker][LEVEL],1.25) / 3.5 - $port[PORT_LEVEL] + $players[$attacker][LEVEL] * .5;
		//linear random value since we take level differences into account in the above eqn.
		$rand = mt_rand(1,100);
		if($rand > $hit) {
			$result[RESULT_OF_WEAPON] = WEAPON_MISS;
			return $result;
		}
	}
	// Drones are weapon id 0 and their damage rolls over
	if(!$weapon) {
		// Calculate how many drones actually fire

		//$percent_attacking = (mt_rand(3, 53) + mt_rand($curr_attacker->level_id / 4, $curr_attacker->level_id)) / 100;
		// Azool's New hotness
		$value1 = pow($players[$attacker][LEVEL],1.85) / 65;
		$value2 = (mt_rand(0,50) + mt_rand(0,50)) / 2;
		$value3 = mt_rand($players[$attacker][LEVEL] / 4, $players[$attacker][LEVEL] * 1.25);
		$drones_percentage = ($value1 + $value2 + $value3) / 100;

		if($drones_percentage < 0) $drones_percentage = 0;
		else if($drones_percentage > 1) $drones_percentage = 1;

		$result[DRONES_FIRED] = ceil($players[$attacker][DRONES_ORIGINAL] * $drones_percentage);
		$potential_damage = 2 * $result[DRONES_FIRED];

		// Yes, they can miss with all drones
		if(!$potential_damage) {
			$result[RESULT_OF_WEAPON] = WEAPON_MISS;
			return $result;
		}
	}
	else {
		$potential_damage = $weapons[$weapon][SHIELD_DAMAGE];
	}

	// Check for port being dead
	if($port[PORT_SHIELDS] == 0 && $port[PORT_ARMOUR] == 0) {
		$result[RESULT_OF_WEAPON] = PORT_DEAD;
		return $result;
	}
	
	// Try to hit shields
	if($port[PORT_SHIELDS] != 0 ) {
		// Does the weapon do shield damage?
		if($potential_damage) {
			// Have we produced more damage than there are shields remaining?
			if($potential_damage >= $port[PORT_SHIELDS])
				$result[SHIELD_DMG_DONE] =  $port[PORT_SHIELDS];
			else
				$result[SHIELD_DMG_DONE] = $potential_damage;

			// If it's an ordinary weapon or drones are out of damage then return
			if($weapon || $result[0] == $potential_damage) {
				$result[RESULT_OF_WEAPON] = NORMAL_HIT;
				return $result;
			}
		}
		else {
			if ($weapon && $weapons[$weapon][ARMOUR_DAMAGE] && $port[PORT_DRONES]) {
				if ($weapons[$weapon][ARMOUR_DAMAGE] > $port[PORT_DRONES] * 60)
					$result[DRONES_HIT_BEHIND_SHIELDS] = $port[PORT_DRONES] * 60;
				else
					$result[DRONES_HIT_BEHIND_SHIELDS] = $weapons[$weapon][ARMOUR_DAMAGE];
				$result[RESULT_OF_WEAPON] = NORMAL_HIT;
				return $result;
			}
			$result[RESULT_OF_WEAPON] = ARMOUR_ON_SHIELD;
			return $result;
		}
	}

	// If a drone shot then adjust damage so we work in units of 1 drone
	if(!$weapon) {
		$potential_damage -= $result[SHIELD_DMG_DONE];
		$potential_damage = 2 * floor($potential_damage/2);

		if($potential_damage == 0) {
			$result[RESULT_OF_WEAPON] = NORMAL_HIT;
			return $result;
		}
	} else
		$potential_damage = $weapons[$weapon][ARMOUR_DAMAGE];

	// No shields left, try to hit their drones
	if($port[PORT_DRONES] != 0 ) {
		// Does the weapon do armour damage?
		if($potential_damage) {
			// Have we produced more damage than there are drones remaining?
			if($potential_damage >= $port[PORT_DRONES] * 3)
				$result[DRONE_DMG_DONE] =  $port[PORT_DRONES] * 3;
			else
				$result[DRONE_DMG_DONE] = $potential_damage;
			// If it's an ordinary weapon or drones are out of damage then return
			if($weapon || $result[DRONE_DMG_DONE] == $potential_damage) {
				$result[RESULT_OF_WEAPON] = NORMAL_HIT;
				return $result;
			}
		} else {
			$result[RESULT_OF_WEAPON] = SHIELD_ON_DRONES;
			return $result;
		}
	}

	// If a drone shot then adjust damage so we work in units of 1 drone
	if(!$weapon) {
		$potential_damage -= $result[1];
		$potential_damage = 2 * floor($potential_damage/2);

		if($potential_damage == 0) {
			$result[RESULT_OF_WEAPON] = NORMAL_HIT;
			return $result;
		}
	} else
		$potential_damage = $weapons[$weapon][ARMOUR_DAMAGE];
		
	//time for armour
	if($port[PORT_ARMOUR] != 0 ) {
		// Does the weapon do armour damage?
		if($potential_damage) {
			// Have we produced more damage than there is armour remaining?
			if($potential_damage >= $port[PORT_ARMOUR]) {
				$result[ARMOUR_DMG_DONE] =  $port[PORT_ARMOUR];
				$result[RESULT_OF_WEAPON] = FINAL_HIT;
				return $result;
			} else {
				$result[ARMOUR_DMG_DONE] = $potential_damage;
				$result[RESULT_OF_WEAPON] = NORMAL_HIT;
				return $result;
			}
		} else {
			$result[RESULT_OF_WEAPON] = SHIELD_ON_ARMOUR;
			return $result;
		}
	}
	return $result;
	
}
function processNews() {
	global $db, $player;
	if (DEBUG) $PHP_OUTPUT.=('News Process<br>');
	$text = '<span class="red bold">*MAYDAY* *MAYDAY*</span> The federal government has received a distress call from the port in sector';
	$text .= ' <span class="yellow">'.$player->getSectorID().'</span>.';
	if ($player->getAllianceID()) {
		$text .= '  It is under attack by members of <span class="yellow">'.$player->getAllianceName().'</span>. ';
		$text .= ' The Federal Government is offering various bounties for the deaths of any raiding members of <span class="yellow">'.$player->getAllianceName().'</span>';
	} else {
		$text .= '  It is under attack by <span class="yellow">'.$player->getPlayerName().'</span>';
		$text .= ' The Federal Government is offering a bounty of ' . round($player->getLevelID() * .4) . ' million credits for the death of <span class="yellow">'.$player->getPlayerName().'</span>';
	}
	$text .= ' prior to the destruction of the port, or until federal forces arrive to defend the port.';
	$db->query('INSERT INTO news (game_id, time, news_message, type,killer_id,killer_alliance,dead_id) VALUES ('.$player->getGameID().', ' . TIME . ', '.$db->escapeString($text).', \'regular\','.$player->getAccountID().','.$player->getAllianceID().','.ACCOUNT_ID_PORT.')');
}
function hofTracker($players, $port) {
	if (DEBUG) $PHP_OUTPUT.=('Tracking HoF<br>');
	global $db, $player;
	//keep track of players who should get credit if the PB is successful.
	$allowed = TIME - 60 * 60 * 3;
	$db->query('DELETE FROM player_attacks_port WHERE time < '.$allowed);
	foreach ($players as $accId => $crap) {
		$db->query('REPLACE INTO player_attacks_port (game_id, account_id, sector_id, time, level) VALUES ' .
					'('.$player->getGameID().', '.$accId.', '.$player->getSectorID().', ' . TIME . ', ' . $port[PORT_LEVEL] . ')');
	}
}
function processResults(&$players, &$port, $fleet, $weapons) {
	if (DEBUG) $PHP_OUTPUT.=('Processing Results<br>');
	global $db, $player;
	$results = array('','',0,0,array());
	//port is updated in downgrade function, all we need to do is format text
	$portDisplay = '<h2>Port Results</h2>';
	$totalPortDamage = 0;
	foreach ($port[PORT_RESULTS] as $resultArray) {
		$totalPortDamage += $resultArray[SHIELD_DMG_DONE] + $resultArray[DRONE_DMG_DONE] + $resultArray[ARMOUR_DMG_DONE];
		$portDisplay .= '<span style="color:yellow;font-variant:small-caps">Port ' . $player->getSectorID() . '</span>';
		if ($resultArray[DRONES_FIRED]) $portDisplay .= ' launches ' . $resultArray[DRONES_FIRED] . ' drones at ';
		else $portDisplay .= ' fires a turret at ';
		if ($resultArray[RESULT_OF_WEAPON] == ALREADY_DEAD) $portDisplay .= 'the debris that used to be ';
		$portDisplay .= $players[$resultArray[TARGET]][PLAYER_NAME];
		if ($resultArray[RESULT_OF_WEAPON] == NORMAL_HIT || $resultArray[RESULT_OF_WEAPON] == FINAL_HIT) $portDisplay .= ' destroying ';
		elseif ($resultArray[RESULT_OF_WEAPON] == WEAPON_MISS) $portDisplay .= ' and misses';
		elseif ($resultArray[RESULT_OF_WEAPON] == ALREADY_DEAD) $portDisplay .= '';
		else $portDisplay .= ' and I have no idea what the hell happens!  Please save this screen and notify Azool.';
		if ($resultArray[SHIELD_DMG_DONE]) $portDisplay .= '<span class="cyan">' . $resultArray[SHIELD_DMG_DONE] . '</span> shields';
		if ($resultArray[DRONE_DMG_DONE]) {
			if ($resultArray[SHIELD_DMG_DONE])
				if ($resultArray[ARMOUR_DMG_DONE]) $portDisplay .= ', ';
				else $portDisplay .= ' and ';
			$portDisplay .= '<span class="yellow">';
			$portDisplay .= floor($resultArray[DRONE_DMG_DONE] / 3);
			$portDisplay .= '</span> drones';
		} if ($resultArray[ARMOUR_DMG_DONE]) {
			if ($resultArray[DRONE_DMG_DONE] || $resultArray[SHIELD_DMG_DONE]) $portDisplay .= ' and ';
			$portDisplay .= '<span class="red">' . $resultArray[ARMOUR_DMG_DONE] . '</span> armour';
		}
		$portDisplay .= '.<br />';
		if ($resultArray[RESULT_OF_WEAPON] == FINAL_HIT) {
			$portDisplay .= '<span style="color:yellow;">' . $players[$resultArray[TARGET]][PLAYER_NAME] . '</span>';
			$portDisplay .= ' is <span style="color:red;">DESTROYED.</span><br />';
		}
		$results[DMG_TO_PLAYER][$resultArray[TARGET]] += $resultArray[SHIELD_DMG_DONE] + $resultArray[DRONE_DMG_DONE] + $resultArray[ARMOUR_DMG_DONE];
	}
	$portDisplay .= '<br /><span style="color:yellow;font-variant:small-caps">Port ' . $player->getSectorID() . '</span>';
	$portDisplay .= ' does a total of <span class="red">'.$totalPortDamage.'</span> damage.<br />';
	$results[PORT_DISPLAY] = $portDisplay;
	$results[TOTAL_PORT_DMG] = $totalPortDamage;
	$playerDisplay = '<h2>Attacker Results</h2>';
	$totalPlayerDamage = 0;
	foreach ($fleet as $accId) {
		$traderDisplay = '';
		$weapon = 0;
		$totalTraderDamage = 0;
		//make sure this element exists to prevent blank messages
		$results[DMG_TO_PLAYER][$accId] += 0;
		foreach ($players[$accId][RESULTS] as $resultArray) {
			$totalTraderDamage += $resultArray[SHIELD_DMG_DONE] + $resultArray[DRONE_DMG_DONE] + $resultArray[ARMOUR_DMG_DONE] + (floor($resultArray[DRONES_HIT_BEHIND_SHIELDS] / 60) * 60);
			$traderDisplay .= $players[$accId][PLAYER_NAME];
			if(!$players[$accId][WEAPONS][$weapon])
				if($resultArray[DRONES_FIRED]) $traderDisplay .= ' launches <span class="yellow">' . $resultArray[DRONES_FIRED] . '</span> drones';
				else $traderDisplay .= ' fails to launch their drones';
			else {
				$traderDisplay .= ' fires their ';
				$traderDisplay .= $weapons[$players[$accId][WEAPONS][$weapon]][WEAPON_NAME];
			}
			$traderDisplay .= ' at ';
			if($resultArray[RESULT_OF_WEAPON] == PORT_DEAD)
				$traderDisplay .= ' the remnants of ';
			$traderDisplay .= '<span style="color:yellow;font-variant:small-caps">Port ' . $player->getSectorID() . '</span>';
			if($resultArray[RESULT_OF_WEAPON] == ARMOUR_ON_SHIELD)
				$traderDisplay .= ' which is deflected by its shields.';
			else if ($resultArray[RESULT_OF_WEAPON] == SHIELD_ON_ARMOUR)
				$traderDisplay .= ' which proves ineffective against its armour.';
			else if ($resultArray[RESULT_OF_WEAPON] == SHIELD_ON_DRONES)
				$traderDisplay .= ' which proves ineffective against its combat drones.';
			else if ($resultArray[RESULT_OF_WEAPON] == WEAPON_MISS && $players[$accId][WEAPONS][$weapon])
				$traderDisplay .= ' and misses every critical system.';
			else if($resultArray[RESULT_OF_WEAPON] == PORT_DEAD)
				$traderDisplay .= '.';
			else {
				$traderDisplay .= ' destroying ';
				if($resultArray[SHIELD_DMG_DONE])
					$traderDisplay .= '<span class="cyan">' . $resultArray[SHIELD_DMG_DONE] . '</span> shields';
				if($resultArray[DRONE_DMG_DONE] || $resultArray[DRONES_HIT_BEHIND_SHIELDS]) {
					if($resultArray[SHIELD_DMG_DONE] && $resultArray[ARMOUR_DMG_DONE])
						$traderDisplay .= ', ';
					elseif ($resultArray[SHIELD_DMG_DONE])
						$traderDisplay .= ' and ';
					$traderDisplay .= '<span class="yellow">';
					if ($resultArray[DRONE_DMG_DONE]) $traderDisplay .= floor($resultArray[DRONE_DMG_DONE] / 3);
					else $traderDisplay .= floor($resultArray[DRONES_HIT_BEHIND_SHIELDS] / 60);
					$traderDisplay .= '</span> drones';
				}
				if ($resultArray[ARMOUR_DMG_DONE]) {
					if ($resultArray[SHIELD_DMG_DONE] || $resultArray[DRONE_DMG_DONE] || $resultArray[DRONES_HIT_BEHIND_SHIELDS])
						$traderDisplay .= ' and ';
					$traderDisplay .= '<span class="red">' . $resultArray[ARMOUR_DMG_DONE] . '</span> armour';
				}
				$traderDisplay .= '.';
			}
			$traderDisplay .= '<br />';
			if ($resultArray[RESULT_OF_WEAPON] == FINAL_HIT) {
				$traderDisplay .= '<span style="color:yellow;font-variant:small-caps">Port ' . $player->getSectorID() . '\'s</span> defenses are ';
				$traderDisplay .= '<span style="color:red;">DESTROYED.</span><br />';
				$traderDisplay .= $players[$accId][PLAYER_NAME] . ' claims <span class="yellow">' . number_format($port[PORT_CREDITS]) . '</span> credits from the port.<br />';
				$players[$accId][CREDITS] += $port[PORT_CREDITS];
				$port[PORT_CREDITS] = 0;
				//get all players involved for HoF
				$allowed = TIME - 60 * 60 * 3;
				$db->query('SELECT * FROM player_attacks_port WHERE game_id = '.$player->getGameID().' AND sector_id = '.$player->getSectorID().' AND time > '.$allowed);
				$temp = array();
				while ($db->next_record()) {
					$currPlayer =& SmrPlayer::getPlayer($db->f('account_id'),SmrSession::$game_id,true);
					$currPlayer->increaseHOF($db->getField('level'),array('Combat','Port','Levels Raided'));
					$currPlayer->increaseHOF(1,array('Combat','Port','Total Raided'));
					$currPlayer->update();
				}
				$db->query('DELETE FROM player_attacks_port WHERE sector_id = '.$player->getSectorID().' AND game_id = '.$player->getGameID());
				// News Entry
				$news = '<span class="yellow smallCaps">Port ' . $player->getSectorID() . '</span> has been successfully raided by ';
				if ($player->getAllianceID()) $news .= 'the members of <span class="yellow">' . $player->getAllianceName() . '</span>';
				else $news .= '<span class="yellow">' . $player->getPlayerName() . '</span>';
				$db->query('INSERT INTO news (game_id, time, news_message, type,killer_id,killer_alliance,dead_id) VALUES ('.$player->getGameID().', ' . TIME . ', '.$db->escapeString($news).', \'regular\','.$player->getAccountID().','.$player->getAllianceID().','.ACCOUNT_ID_PORT.')');
				// Trigger gets an alignment change and a bounty if port is taken
				$db->query('SELECT * FROM bounty WHERE game_id = '.$player->getGameID().' AND account_id = '.$player->getAccountID().' ' .
					'AND claimer_id = 0 AND type = \'HQ\'');
				$amount = $player->getExperience() * $port[PORT_LEVEL];
				if ($db->next_record() && $amount > 0) {
					$bounty_id = $db->f('bounty_id');
					$curr_amount = $db->f('amount');
					$new_amount = $curr_amount + $amount;
					$db->query('UPDATE bounty SET amount = '.$new_amount.', time = ' . TIME . ' WHERE game_id = '.$player->getGameID().' AND bounty_id = '.$bounty_id);
				} elseif ($amount > 0) {
					$db->query('INSERT INTO bounty (account_id, game_id, type, amount, claimer_id, time) VALUES ' .
						'('.$player->getAccountID().', '.$player->getGameID().', \'HQ\', '.$amount.', 0, ' . TIME . ')');
				}
				if($port[PORT_RACE_ID] > 1) {
					$new_relations = $player->getRelation($port[PORT_RACE_ID]) - 45;
					if ($new_relations < -500) $new_relations = -500;
					$db->query('REPLACE INTO player_has_relation (account_id, game_id, race_id, relation) VALUES('.$player->getAccountID().', '.$player->getGameID().', ' . $port[PORT_RACE_ID] . ', '.$new_relations.')');
				}
				// also we change alignment
				if ($player->relations_global_rev[$port[PORT_RACE_ID]] < -299)
				   $new_alignment = $player->getAlignment() + $port[PORT_LEVEL] * 2;
				else
				   $new_alignment = $player->getAlignment() - $port[PORT_LEVEL] * 2;
				$db->query('UPDATE player SET alignment='.$new_alignment.' WHERE account_id='.$player->getAccountID().' AND game_id='.$player->getGameID().' LIMIT 1');
			}
			$weapon++;
		}
		$totalPlayerDamage += $totalTraderDamage;
		$currPlayer =& SmrPlayer::getPlayer($accId,SmrSession::$game_id,true);
		$currPlayer->increaseHOF($totalTraderDamage,array('Combat','Port','Damage Done'));
		$currPlayer->update();
		$players[$accId][EXPERIENCE_GAINED] = round($totalTraderDamage * .05);
		$traderDisplay .= '<br />' . $players[$accId][PLAYER_NAME] . ' does a total of <span class="red">'.$totalTraderDamage.'</span> damage.<br />';
		//append this display to the overal display.
		$playerDisplay .= $traderDisplay . '<br />';
		$traderDisplay = '<span style="color:yellow;font-variant:small-caps">Port ' . $player->getSectorID() . '</span>\'s defenses ' . 
							'do a total of <span class="red">'.$totalPortDamage.'</span> damage. <span class="red">' . 
							$results[DMG_TO_PLAYER][$accId] . '</span> of which hit you.<br /><br />' . $traderDisplay . 
							'You have gained <span class="blue">' . round($totalTraderDamage * .05) . '</span> experience.';
		if ($accId != $player->getAccountID())
			$db->query('REPLACE INTO sector_message (account_id, game_id, message) VALUES ('.$accId.', '.$player->getGameID().', ' . $db->escape_string($traderDisplay) . ')');
	}
	$playerDisplay .= 'This team does a total of <span class="red">'.$totalPlayerDamage.'</span> damage.<br />';
	$results[PLAYER_DISPLAY] = $playerDisplay;
	$results[TOTAL_PLAYER_DMG] = $totalPlayerDamage;
	//we need to update the database for the players
	$temp = array();
	$ships = array();
	$hqs = array();
	foreach ($players as $accId => $playerArray) {
		if ($playerArray[SHIELDS] == 0 && $playerArray[ARMOUR] == 0) {
			$temp[] = $accId;			
			$ships[] = $players[$accId][SHIP_ID];
			$hqs[] = $players[$accId][RACE_ID];
		} else {
			$db->query('UPDATE ship_has_hardware SET amount=' . $players[$accId][SHIELDS] . ' WHERE hardware_type_id=1 AND account_id=' . $accId . ' AND game_id=' . $player->getGameID() . ' LIMIT 1');
			$db->query('UPDATE ship_has_hardware SET amount=' . $players[$accId][ARMOUR] . ' WHERE hardware_type_id=2 AND account_id=' . $accId . ' AND game_id=' . $player->getGameID() . ' LIMIT 1');
			$db->query('UPDATE ship_has_hardware SET amount=' . $players[$accId][DRONES] . ' WHERE hardware_type_id=4 AND account_id=' . $accId . ' AND game_id=' . $player->getGameID() . ' LIMIT 1');
			$db->query('UPDATE player SET experience = experience + ' . $players[$accId][EXPERIENCE_GAINED] . ', credits = ' . $players[$accId][CREDITS] . ' WHERE account_id = '.$accId.' AND game_id = '.$player->getGameID());
		}
	}
	podPlayers($temp, $ships, $hqs, $port, $players);
	unset($temp);
	return $results;
}
function podPlayers($IDArray, $ships, $hqs, $port, $players) {
	if (DEBUG) $PHP_OUTPUT.=('Podding Players<br>');
	global $db, $session, $player;
	if (!sizeof($IDArray)) return;
	$hqs = build_hqs(array_unique($hqs));
	$ships = build_ships(array_unique($ships));
	foreach ($IDArray as $accId) {
		// Escape pod speed is 7
		$turns = ceil($players[$accId][TURNS] * (7 / $ships[$players[$accId][SHIP_ID]][1]));
		if($turns > MAX_TURNS) {
			$turns = MAX_TURNS;
		}
		$newExp = $players[$accId][EXPERIENCE] * .67;
		$sectorId = $hqs[$players[$accId][RACE_ID]];
		$insurance = ceil($ships[$players[$accId][SHIP_ID]][0] * 0.25);
		if($insurance < 5000) $insurance = 5000;
		$db->query('UPDATE player SET ship_type_id = 69, turns = '.$turns.', newbie_turns = 100, ' . 
					'deaths = deaths + 1, dead = \'TRUE\', sector_id = '.$sectorId.', credits = '.$insurance.', experience = '.$newExp.' ' . 
					'WHERE game_id = '.$player->getGameID().' AND account_id = '.$accId);
					
		$currPlayer =& SmrPlayer::getPlayer($accId,SmrSession::$game_id,true);
		$currPlayer->increaseHOF(1,array('Dying','Deaths'));
		$currPlayer->update();
		$db->query('UPDATE ship_has_hardware SET amount=50 WHERE (hardware_type_id=1 OR hardware_type_id=2) AND account_id=' . $accId . ' AND game_id=' . $player->getGameID() . ' LIMIT 2');
		$db->query('DELETE FROM ship_has_hardware WHERE hardware_type_id=4 AND account_id=' . $accId . ' AND game_id=' . $player->getGameID() . ' LIMIT 1');
		$msg = $players[$accId][PLAYER_NAME];
		$db->query('SELECT * FROM ship_has_name WHERE account_id = '.$accId.' AND game_id = '.$player->getGameID());
		if ($db->next_record()) $ship_names[$accId] = $db->f('ship_name');
		if(isset($ship_names[$accId])) {
			$msg .= ' flying ';
			if(!stristr($ship_names[$accId],'<img')){
				if(stristr($ship_names[$accId],'<mar')) $msg .= '<span class="yellow">' . strip_tags($ship_names[$accId]) . '</span>';
				else $msg .= $ship_names[$accId];
			} else $msg .= strip_tags($ship_names[$accId], '<img>');
		}
		
		$msg .= ' was destroyed while invading ';
		$msg .= '<span style="color:yellow;font-variant:small-caps">Port ' . $player->getSectorID() . '</span>.';
		$db->query('INSERT INTO news (game_id, time, news_message,killer_id,dead_id,dead_alliance) VALUES ('.$player->getGameID().', ' . TIME . ', '.$db->escapeString($msg).','.ACCOUNT_ID_PORT.','.$currPlayer->getAccountID().','.$currPlayer->getAllianceID().')');
		
		$killer_id = 0;
		
		$temp = 'You were <span class="red">DESTROYED</span> by <span style="color:yellow;font-variant:small-caps">Port ' . $player->getSectorID() . '</span>\'s defenses.';
		$msg = '(' . SmrSession::$game_id . ',' . $accId . ',2,' . $db->escape_string($temp) . ',' . $killer_id . ',' . TIME . ',\'FALSE\',' . MESSAGE_EXPIRES . ')';
		$db->query('INSERT INTO message (game_id, account_id, message_type_id, message_text, sender_id, send_time, msg_read, expire_time) VALUES '.$msg);
		$db->query('INSERT INTO player_has_unread_messages (account_id, game_id, message_type_id) VALUES ('.$accId.', '.SmrSession::$game_id.', 2)');
		unset($temp);
	}
	//Deal with hardware, cloaks etc for podded players
	$num_podded = count($IDArray);
	if($num_podded) {
		$podded_in = implode(',',$IDArray);
		$db->query('DELETE FROM ship_has_weapon WHERE account_id IN (' . $podded_in . ') AND game_id=' . SmrSession::$game_id);
		$db->query('DELETE FROM ship_has_cargo WHERE account_id IN (' . $podded_in . ') AND game_id=' . SmrSession::$game_id);
		$db->query('DELETE FROM ship_has_illusion WHERE account_id IN (' . $podded_in . ') AND game_id=' . SmrSession::$game_id);
		$db->query('DELETE FROM player_plotted_course WHERE account_id IN (' . $podded_in . ') AND game_id=' . SmrSession::$game_id . ' LIMIT ' . $num_podded);
		$db->query('DELETE FROM ship_has_hardware WHERE account_id IN (' . $podded_in . ') AND hardware_type_id>4 AND game_id=' . SmrSession::$game_id);
		$db->query('UPDATE ship_has_hardware SET amount=5 WHERE account_id IN (' . $podded_in . ') AND hardware_type_id=3  AND game_id=' . SmrSession::$game_id . ' LIMIT ' . $num_podded);
		$db->query('UPDATE ship_has_hardware SET old_amount=amount WHERE account_id IN (' . $podded_in . ') AND game_id=' . SmrSession::$game_id);
	}
}
function sendReport($results, $port) {
	if (DEBUG) $PHP_OUTPUT.=('Sending Reports<br>');
	global $player, $db;
	$mainText = 'From the reports we have been able to gather the following information:<br /><br />';
	$mainText .= $results[PORT_DISPLAY] . '<br />' . $results[PLAYER_DISPLAY];
	if ($player->getAllianceID() > 0) {
		$topic = 'Port Siege Report Sector '.$player->getSectorID();
		$text = 'Reports have come in from the space above <span class="yellow">Port ' . $player->getSectorID() . '</span> and have confirmed our <span class="red">siege</span>!<br />';
		$text .= $mainText;
		$thread_id = 0;
		$db->query('SELECT * FROM alliance_thread_topic WHERE game_id = '.$player->getGameID().' AND alliance_id = '.$player->getAllianceID().' AND topic = '.$db->escapeString($topic).' LIMIT 1');
		if ($db->next_record()) $thread_id = $db->f('thread_id');
		if ($thread_id == 0) {
			$db->query('SELECT * FROM alliance_thread_topic WHERE game_id = '.$player->getGameID().' AND alliance_id = '.$player->getAllianceID().' ORDER BY thread_id DESC LIMIT 1');
			if ($db->next_record())
				$thread_id = $db->f('thread_id') + 1;
			else $thread_id = 1;
			$db->query('INSERT INTO alliance_thread_topic (game_id, alliance_id, thread_id, topic) VALUES ' .
						'('.$player->getGameID().', '.$player->getAllianceID().', '.$thread_id.', '.$db->escapeString($topic).')');
		}
		$db->query('SELECT * FROM alliance_thread WHERE alliance_id = '.$player->getAllianceID().' AND game_id = '.$player->getGameID().' AND ' .
					'thread_id = '.$thread_id.' ORDER BY reply_id DESC LIMIT 1');
		if ($db->next_record()) $reply_id = $db->f('reply_id') + 1;
		else $reply_id = 1;
		$db->query('INSERT INTO alliance_thread (game_id, alliance_id, thread_id, reply_id, text, sender_id, time) VALUES ' .
				'('.$player->getGameID().', '.$player->getAllianceID().', '.$thread_id.', '.$reply_id.', '.$db->escapeString($text).', 0, ' . TIME . ')');
	}
}
function portDowngrade(&$results, &$port) {
	if (DEBUG) $PHP_OUTPUT.=('Downgrading<br>');
	global $db, $player;
	if ($port[PORT_LEVEL] > 1) {
		$numberOfChances = floor($results[TOTAL_PLAYER_DMG] / 500);
		for ($i = 0; $i < $numberOfChances; $i++) {
			if (mt_rand(1, 100) <= 5) {
				$db->query('SELECT count(*) as numGoods FROM port_has_goods WHERE game_id = '.$player->getGameID().' AND sector_id = '.$player->getSectorID());
				$db->next_record();
				if ($db->f('numGoods') >= 3) {
	
		            // get last good for this port
	    	        $db->query('SELECT good_id FROM port_has_goods ' .
	                         'WHERE game_id = '.$player->getGameID().' AND ' .
	                               'sector_id = '.$player->getSectorID().' ' .
	                               'ORDER BY good_id DESC ' .
	                               'LIMIT 1');
	        	    if ($db->next_record())
	            	    $good_id = $db->f('good_id');
		            // delete it from db
	    	        $db->query('DELETE FROM port_has_goods ' .
	                         'WHERE game_id = '.$player->getGameID().' AND ' .
	                               'sector_id = '.$player->getSectorID().' AND ' .
	                               'good_id = '.$good_id.' LIMIT 1');
		        }
		        $port[PORT_LEVEL] -= 1;
				$results[PLAYER_DISPLAY] .= '<br />The port has lost a level.';
				//only one downgrade per shot
				$i = $numberOfChances;
			}
	   }	
	}
    $db->query('UPDATE port SET shields = ' . $port[PORT_SHIELDS] . ', armour = ' . $port[PORT_ARMOUR] . ', combat_drones = ' . $port[PORT_DRONES] .
    			', level = ' . $port[PORT_LEVEL] . ', credits = ' . $port[PORT_CREDITS] . ', attack_started = ' . $port[STARTED] . ', reinforce_time = ' . $port[REFRESH] .
    			' WHERE sector_id = '.$player->getSectorID().' AND game_id = '.$player->getGameID());
}
function doLog($results) {
	if (DEBUG) $PHP_OUTPUT.=('Logging<br>');
	global $account, $player;
	$account->log(7, 'Player attacks port their team does ' . $results[TOTAL_PLAYER_DMG], $player->getSectorID());
}
function checkContinue($players, $port) {
	if (DEBUG) $PHP_OUTPUT.=('Continue?<br>');
	global $player;
	if ($players[$player->getAccountID()][KILLER]) return FALSE;
	if ($port[PORT_ARMOUR] == 0) return FALSE;
	return TRUE;
}
if ($player->getNewbieTurns()) create_error('You can\'t shoot at that port while under newbie protection.');
if ($player->getTurns() < 3) create_error('Insufficient turns to perform that action');
if (DEBUG) $PHP_OUTPUT.=('Opening<br>');
//$players contains all player info for the trigger and his alliance IS
$players = getPlayerArray();
//$weapons contains info for weapons being used this battle
$weapons = getWeapons(getHardware($players));
if (!sizeof($players[$player->getAccountID()][WEAPONS])) create_error('What are you going to do?  Insult it to death?');
$port = getPortArray();
//in case there are more than 10 players IS.  $fleet contains account_ids of the attackers
$fleet = getFleet($players, $weapons);
if (DEBUG) $PHP_OUTPUT.=('Pre news<br>');
hofTracker($players, $port);
// Take off the 3 turns for attacking
$player->takeTurns(3);
$player->update();
// fire shots
if (DEBUG) $PHP_OUTPUT.=('Pre Shots<br>');
portFires($fleet,$port,$players);
fleetFires($fleet,$port,$players,$weapons);
//get results in a way we want them
$results = processResults($players, $port, $fleet, $weapons);
//post on alliances MB or send to player
portDowngrade($results, $port);
sendReport($results, $port);
//log player
doLog($results);
//insert into combat logs
$finalResults = $results[0] . '<br /><img src="images/portAttack.jpg" width="480px" height="330px" alt="Port Attack" title="Port Attack"><br />' . $results[1];
$db->query('INSERT INTO combat_logs VALUES(\'\',' . SmrSession::$game_id . ',\'PORT\',' . $player->getSectorID() . ',' . TIME . ',' . SmrSession::$account_id . ',' . $player->getAllianceID() . ',0,0,' . $db->escapeBinary(gzcompress($finalResults)) . ', \'FALSE\')');
if (DEBUG) $PHP_OUTPUT.=('Pre Forward/Display<br>');
$container=array();
$container['url'] = 'skeleton.php';
$container['body'] = 'port_attack.php';
$container['results'] = $results;
if ($players[$player->getAccountID()][KILLER]) $container['override_death'] = TRUE;
$container['continue'] = checkContinue($players, $port);

SmrPlayer::refreshCache();
SmrShip::refreshCache();
SmrPort::refreshCache();

forward($container);

?>
