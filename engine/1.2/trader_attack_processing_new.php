<?php

// TODO: Cleanup and finalise
define ('MAX_TURNS', ($player->game_speed * 400));
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

define('TIME', time());
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

	// No drones left, try to hit their armor
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

function build_weapons($weapon_ids) {
	global $db,$session;

	$weapons = array();
	
	if(empty($weapon_ids)) {
		return $weapons;
	}	
	
	$db->query('SELECT weapon_type_id,weapon_name,shield_damage,armor_damage,accuracy FROM weapon_type WHERE weapon_type_id IN (' . implode(',',$weapon_ids) . ') LIMIT ' . count($weapon_ids));
	
	while($db->next_record()) {
		$weapons[$db->f('weapon_type_id')] = array(
												$db->f('weapon_name'),
												(int)$db->f('shield_damage'),
												(int)$db->f('armor_damage'),
												(int)$db->f('accuracy')
												);
	}	
	
	return $weapons;
}

function players_build_hardware(&$players) {
	global $db, $session;
	
	$weapon_ids = array();
	
	$players_in = implode(',',array_keys($players));
	
	$db->query('SELECT account_id,weapon_type_id FROM ship_has_weapon WHERE account_id IN (' . $players_in . ') AND game_id=' . SmrSession::$game_id . ' ORDER BY order_id ASC');

	while($db->next_record()) {
		$weapon_ids[] = $db->f('weapon_type_id');
		$players[$db->f('account_id')][WEAPONS][] = (int)$db->f('weapon_type_id');
	}
	
	$db->query('SELECT hardware_type_id,account_id,amount FROM ship_has_hardware WHERE account_id IN (' . $players_in . ') AND (hardware_type_id=' . HARDWARE_SHIELDS . ' OR hardware_type_id=' . HARDWARE_ARMOR . ' OR hardware_type_id=' . HARDWARE_COMBAT . ' OR hardware_type_id=' . HARDWARE_DCS . ') AND game_id=' . SmrSession::$game_id);
	
	while($db->next_record()) {
		switch($db->f('hardware_type_id')) {
		case(HARDWARE_SHIELDS):
			$players[$db->f('account_id')][SHIELDS] = (int)$db->f('amount');
			break;
		case(HARDWARE_ARMOR):
			$players[$db->f('account_id')][ARMOR] = (int)$db->f('amount');
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
	
	return array_unique($weapon_ids);
	
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
	AND sector_id=' . $player->sector_id . '
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
			if($account_id == $player->account_id) {
				$container=array();
				$container['url'] = 'skeleton.php';
				$container['body'] = 'current_sector.php';
				$container['msg'] = '<span class="red bold">ERROR:</span> You are under federal protection.';
				forward($container);
				exit;
			}
			else if($account_id == $var['target']) {
				$container=array();
				$container['url'] = 'skeleton.php';
				$container['body'] = 'current_sector.php';
				$container['msg'] = '<span class="red bold">ERROR:</span> Target is under federal protection.';
				forward($container);
				exit;
			}
			unset ($players[$account_id]);
		}
		else if($account_id != $player->account_id && $account_id != $var['target']) {
			// We add the player and target to the fleets after capping
			if(in_array($players[$account_id][ALLIANCE_ID], $attackers)) {
				$fleets[0][] = $account_id;
			} else {
				$fleets[1][] = $account_id;
			}				
		}
	}

	// Cap fleets to the required size
	
	for($i=0;$i<2;++$i) {
		$fleet_size = count($fleets[$i]);
		if($fleet_size > (MAXIMUM_FLEET_SIZE - 1)) {
			// We shuffle to stop the same people being capped all the time
			shuffle($fleets[$i]);
			$temp = array();
			$count = 0;
			for($j=0;$j<$fleet_size;++$j) {
				if($count < MAXIMUM_FLEET_SIZE - 1) {
					$temp[] = $fleets[$i][$j];
				}
				else {
					unset($players[$fleets[$i][$j]]);
				}
				++$count;
			}
			$fleets[$i] = $temp;
		}
	}

	// Add the inital combatants to their respective fleets
	$fleets[0][] = (int)SmrSession::$old_account_id;
	$fleets[1][] = (int)$var['target'];

	// Shuffle for random firing order
	shuffle($fleets[0]);
	shuffle($fleets[1]);
	
	return $fleets;
}

function players_init(&$attackers, &$defenders) {
	global $db, $session,$var, $player, $account;
	
	// Get the player we're attacking
	$db->query('SELECT land_on_planet,newbie_turns,dead,sector_id,account_id,player_id,player_name,race_id,alignment,ship_type_id,experience,alliance_id,credits,turns FROM player WHERE account_id=' . $var['target'] . ' AND game_id=' . SmrSession::$game_id . ' LIMIT 1');

	$db->next_record();

	if($player->turns < 3) {
		$container=array();
		$container['url'] = 'skeleton.php';
		$container['body'] = 'current_sector.php';
		$container['msg'] = '<span class="red bold">ERROR:</span> You have insufficient turns to perform that action.';
		forward($container);
		exit;
	}
	else if($db->f('dead') == 'TRUE') {
		$container=array();
		$container['url'] = 'skeleton.php';
		$container['body'] = 'current_sector.php';
		$container['msg'] = '<span class="red bold">ERROR:</span> Target already dead.';
		forward($container);
		exit;
	}
	else if($db->f('sector_id') != $player->sector_id) {
		$container=array();
		$container['url'] = 'skeleton.php';
		$container['body'] = 'current_sector.php';
		$container['msg'] = '<span class="red bold">ERROR:</span> Target is no longer in this sector.';
		forward($container);
		exit;
	}
	else if($db->f('newbie_turns')) {
		$container=array();
		$container['url'] = 'skeleton.php';
		$container['body'] = 'current_sector.php';
		$container['msg'] = '<span class="red bold">ERROR:</span> Target is under newbie protection.';
		forward($container);
		exit;
	}
	else if($db->f('land_on_planet') == 'TRUE') {
		$container=array();
		$container['url'] = 'skeleton.php';
		$container['body'] = 'current_sector.php';
		$container['msg'] = '<span class="red bold">ERROR:</span> Target is protected by planetary shields.';
		forward($container);
		exit;
	}

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
		0,0,0,0,0,false,array(),array(),0,0,0,array(),0,0,0,0
	);

	// Insert our own player into the players array
	$players[SmrSession::$old_account_id] = array(
		(int)$player->player_id,
		get_colored_text($player->alignment,$player->player_name . ' (' . $player->player_id . ')'),
		(int)$player->alliance_id,
		(int)$player->race_id,
		(int)$player->credits,
		(int)$player->turns,
		(int)$player->alignment,
		(int)$player->ship_type_id,
		(int)$player->experience,
		0,0,0,0,0,false,array(),array(),0,0,0,array(),0,0,0,0
	);

// remove newbie gals
//	// Get the galaxy name and id
//	$db->query('SELECT
//	galaxy_id
//	FROM sector
//	WHERE sector_id=' . $player->sector_id . '
//	AND game_id=' . SmrSession::$game_id . '
//	LIMIT 1');
//
//	$db->next_record();
//	if($db->f('galaxy_id') < 9) {
//		$protection = TRUE;
//	}
//	else {
		$protection = FALSE;
//	}

	if($player->alliance_id || $players[$var['target']][ALLIANCE_ID]) {
		//get treaty info
		$treaties_attacker = array(array(),array(),array());
		$treaties_defender = array(array(),array(),array());
		$db->query("SELECT alliance_id_1, alliance_id_2, trader_assist, trader_defend, trader_nap FROM alliance_treaties
					WHERE game_id = $player->game_id
					AND (alliance_id_1 = " . $players[$var['target']][ALLIANCE_ID] . " OR alliance_id_1 = $player->alliance_id
					OR alliance_id_2 = " . $players[$var['target']][ALLIANCE_ID] . " OR alliance_id_2 = $player->alliance_id)
					AND (trader_assist = 1 OR trader_defend = 1)
					AND official = 'TRUE'");
		while ($db->next_record()) {
			if ($db->f("alliance_id_1") == $player->alliance_id) {
				if ($db->f("trader_nap")) $treaties_attacker[NAP][$db->f("alliance_id_2")] = $db->f("alliance_id_2");
				if ($db->f("trader_assist")) $treaties_attacker[ASSIST][$db->f("alliance_id_2")] = $db->f("alliance_id_2");
			} elseif ($db->f("alliance_id_2") == $player->alliance_id) {
				if ($db->f("trader_nap")) $treaties_attacker[NAP][$db->f("alliance_id_1")] = $db->f("alliance_id_1");
				if ($db->f("trader_assist")) $treaties_attacker[ASSIST][$db->f("alliance_id_1")] = $db->f("alliance_id_1");
			} elseif ($db->f("alliance_id_1") == $players[$var['target']][ALLIANCE_ID]) {
				if ($db->f("trader_nap")) $treaties_defender[NAP][$db->f("alliance_id_2")] = $db->f("alliance_id_2");
				if ($db->f("trader_defend")) $treaties_defender[DEFEND][$db->f("alliance_id_2")] = $db->f("alliance_id_2");
			} elseif ($db->f("alliance_id_2") == $players[$var['target']][ALLIANCE_ID]) {
				if ($db->f("trader_nap")) $treaties_defender[NAP][$db->f("alliance_id_1")] = $db->f("alliance_id_1");
				if ($db->f("trader_defend")) $treaties_defender[DEFEND][$db->f("alliance_id_1")] = $db->f("alliance_id_1");
			}
		}
		$attackers[] = $player->alliance_id;
		$defenders[] = $players[$var['target']][ALLIANCE_ID];
		foreach ($treaties_attacker[ASSIST] as $allID) {
			if (!isset($treaties_defender[NAP][$allID])) $attackers[] = $allID;
		}
		foreach ($treaties_defender[DEFEND] as $allID) {
			if (!isset($treaties_attacker[NAP][$allID])) $defenders[] = $allID;
		}
		
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
		FROM player';

		if ($protection) {
			$query .= ',account_has_stats,account	
				WHERE account.account_id = player.account_id
				AND account_has_stats.account_id = player.account_id';

			if($account->get_rank() > BEGINNER || $account->veteran == 'TRUE') {
				$query2 = ' AND (
				(account_has_stats.kills >= 15 OR account_has_stats.experience_traded >= 60000) OR 
				(account_has_stats.kills >= 10 AND account_has_stats.experience_traded >= 40000)
				OR account.veteran="TRUE")';
			}
			else {
				$query2 = ' AND (
				(account_has_stats.kills < 15 AND account_has_stats.experience_traded < 60000) OR 
				(account_has_stats.kills < 10 AND account_has_stats.experience_traded < 40000)
				) AND account.veteran="FALSE"';
			}
			$query2 .= ' AND ';
		}
		else {
			$query2 = ' WHERE ';
		}

		$query .= $query2 . 'player.sector_id=' . $player->sector_id . '
			AND player.account_id!=' . SmrSession::$old_account_id . '
			AND player.account_id!=' . $var['target'] . '
			AND player.game_id=' . SmrSession::$game_id . ' 
			AND player.land_on_planet="FALSE" 
			AND player.newbie_turns=0
			AND player.last_active>' .  (time() - 259200);

		if($player->alliance_id && $players[$var['target']][ALLIANCE_ID]) {
			$query .= ' AND (player.alliance_id IN (' . implode(',', $attackers) . ')';
			$query .= ' OR player.alliance_id IN (' . implode(',', $defenders) . '))';
		} else if($player->alliance_id) {
			$query .= ' AND player.alliance_id IN (' . implode(',', $attackers) . ')';
		} else if($players[$var['target']][ALLIANCE_ID]) {
			$query .= ' AND player.alliance_id IN (' . implode(',', $defenders) . ')';
		}

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
				0,0,0,0,0,false,array(),array(),0,0,0,array(),0,0,0,0
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
						$results .= '<span class="red">' . $result[2] . '</span> plates of armor';
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

function process_news(&$players,&$killed_id,&$ship_names) {

	global $session,$player,$db;
	
	$killer_id = $players[$killed_id][KILLER];
	$msg = $players[$killed_id][PLAYER_NAME];
	
	if(isset($ship_names[$killed_id])) {
		$msg .= ' flying ';
		if(!stristr($ship_names[$killed_id],'<img')){
			if(stristr($ship_names[$killed_id],'<mar')) $msg .= "<span class=\"yellow\">" . strip_tags($ship_names[$killed_id]) . "</span>";
			else {
				$msg .= $ship_names[$killed_id];
			}
		} else $msg .= strip_tags($ship_names[$killed_id], '<img>');
	}
	/*
	if(isset($ship_names[$killed_id])) {
		$msg .= ' flying <span class="yellow">';
		$msg .= strip_tags($ship_names[$killed_id]);
		$msg .= '</span> ';
	}
	*/
	$msg .= ' was destroyed by ';
	$msg .= $players[$killer_id][PLAYER_NAME];
	if(isset($ship_names[$killer_id])) {
		$msg .= ' flying ';
		if(!stristr($ship_names[$killer_id],'<img')){
			if(stristr($ship_names[$killer_id],'<mar')) $msg .= "<span class=\"yellow\">" . strip_tags($ship_names[$killer_id]) . "</span>";
			else {
				$msg .= $ship_names[$killer_id];
			}
		} else $msg .= strip_tags($ship_names[$killer_id], '<img>');
	}
	/*
	if(isset($ship_names[$killer_id])) {
		$msg .= ' flying <span class="yellow">';
		$msg .= strip_tags($ship_names[$killer_id]);
		$msg .= '</span> ';
	}
	*/
 	$msg .= ' in Sector&nbsp#' . $player->sector_id;

	return '(' . SmrSession::$game_id . ',' . TIME . ',"' . $db->escape_string($msg) . '","regular")';
}

function process_messages(&$players,$killed_id) {

	global $session,$player,$db;
	
	$killer_id = $players[$killed_id][KILLER];
	
	$temp = 'You were <span class="red">DESTROYED</span> by ' . $players[$killer_id][PLAYER_NAME] . ' in sector <span class="blue">#' . $player->sector_id . '</span>';
	$msg .= '(' . SmrSession::$game_id . ',' . $killed_id . ',2,"' . $db->escape_string($temp) . '",' . $killer_id . ',' . TIME . ',"FALSE",' . MESSAGE_EXPIRES . ')';
	$temp = 'You <span class="red">DESTROYED</span> ' . $players[$killed_id][PLAYER_NAME] . ' in sector <span class="blue">#' . $player->sector_id . '</span>';
	$msg .= ',(' . SmrSession::$game_id . ',' . $killer_id . ',2,"' . $db->escape_string($temp) . '",' . $killed_id . ',' . TIME . ',"FALSE",' . MESSAGE_EXPIRES . ')';

	return $msg;	
}

function process_unread_messages(&$players,$killed_id) {

	global $session,$player;
	
	$killer_id = $players[$killed_id][KILLER];
	
	$msg .= '(' . $killed_id . ',' . $player->game_id . ',2)';
	$msg .= ',(' . $killer_id . ',' . $player->game_id . ',2)';

	return $msg;	
}

function process_death(&$players,&$killed_id,&$ships,&$relations) {
	global $player, $db;
	$killer_id = $players[$killed_id][KILLER];

	// Dead player loses experience
	// We're nice so we allow them to keep xp from damage/kills this round
	$exp_loss_percentage = 0.20 + (($players[$killed_id][LEVEL] - $players[$killer_id][LEVEL]) * 0.005);
	if($exp_loss_percentage > 0) {
		$players[$killed_id][EXPERIENCE_GAINED] -= floor($players[$killed_id][EXPERIENCE] * $exp_loss_percentage);
		$players[$killed_id][EXPERIENCE_LOST] = floor($players[$killed_id][EXPERIENCE] * $exp_loss_percentage);
	}

	// The person who got the kill gains experience (Dropped to 75% of previous incarnation)
	if($players[$killed_id][LEVEL] >= $players[$killer_id][LEVEL]) {
		$xp_gain = ceil(0.75*((((($players[$killed_id][LEVEL] - $players[$killer_id][LEVEL]) / $players[$killed_id][LEVEL]) + 1) * 0.04 * $players[$killed_id][EXPERIENCE]) + (0.025 * $players[$killed_id][EXPERIENCE]))); 
		$players[$killer_id][EXPERIENCE_GAINED] += $xp_gain;
	}
	else {
		$xp_gain = ceil(0.75*((((($players[$killed_id][LEVEL] - $players[$killer_id][LEVEL]) / $players[$killer_id][LEVEL]) + 1) * 0.04 * $players[$killed_id][EXPERIENCE]) + (0.025 * $players[$killed_id][EXPERIENCE]))); 
		$players[$killer_id][EXPERIENCE_GAINED] += $xp_gain;
	}

	// They lose all their cash and receive insurance cash
	$insurance = ceil($ships[$players[$killed_id][SHIP_ID]][0] * 0.25);
	if($insurance < 5000) $insurance = 5000;
	$players[$killed_id][CREDITS_GAINED] = $insurance - $players[$killed_id][CREDITS];	
	
	// Their killer may change alignment
	$relation = $relations[$players[$killer_id][RACE_ID]][$players[$killed_id][RACE_ID]];
	if($relation >= 300 || $relation <= -300) {
		$players[$killer_id][ALIGNMENT_GAINED] -= $relation * 0.04;
		// War setting gives them military pay
		if($relation <= -300) {
			$players[$killer_id][MILITARY_GAINED] -= floor($relation * 100 * (pow(($xp_gain * 0.5),0.25)));
		}
	}
	else {
		$players[$killer_id][ALIGNMENT_GAINED] -= $relation * 0.1;
	}
	//check for federal bounty being offered for current port raiders
	$allowed = TIME - 60 * 60 * 3;
	$db->query("DELETE FROM player_attacks_port WHERE time < $allowed");
	$query = 'SELECT count(*) as numAttacks
				FROM player_attacks_port, player, port
				WHERE player_attacks_port.game_id = port.game_id
				AND port.game_id = player.game_id
				AND armor > 0
				AND player_attacks_port.sector_id = port.sector_id
				AND player.account_id = player_attacks_port.account_id
				AND player.account_id = ' . $killed_id . '
				AND player.game_id = ' . $player->game_id;
	$db->query($query);
	if ($db->next_record()) {
		if ($db->f("numAttacks")) {
			$numAttacks = $db->f("numAttacks");
			$multiplier = round(.4 * $players[$killed_id][LEVEL]);
			$portBounty = $numAttacks * 1000000 * $multiplier;
			$db->query("INSERT INTO bounty (account_id, game_id, type, amount, claimer_id, time) VALUES ($killed_id, $player->game_id, 'HQ', " . 
						"$portBounty, $killer_id, " . TIME . ")");
		}
	}
}

function process_bounty(&$players,&$killed_id,&$bounties) {
	
	$killer_id = $players[$killed_id][KILLER];
	
	// Killer get marked as claimer of podded player's bounties even if they don't exist
	$bounties[$killed_id][1][1] = $killer_id;
	$bounties[$killed_id][0][1] = $killer_id;

	// If the alignment difference is greater than 200 then a bounty may be set
	$alignment_diff = abs($players[$killed_id][ALIGNMENT] - $players[$killer_id][ALIGNMENT]);
	if($alignment_diff >= 200) {
		// If the podded players alignment makes them deputy or member then set bounty
		if($players[$killed_id][ALIGNMENT] >= 100) {
			$bounty_type = 0;
		}
		else if ($players[$killed_id][ALIGNMENT] <= 100) {
			$bounty_type = 1;
		}
		
		if(isset($bounty_type)) {
			// If the attacker already has a bounty then add the old bounty
			if(isset($bounties[$killer_id][$bounty_type])) {
				$bounties[$killer_id][$bounty_type][2] += floor(pow($alignment_diff, 2.56));
			}
			// If not create a new one
			else {
				$bounties[$killer_id][$bounty_type][2] = floor(pow($alignment_diff, 2.56));
				$bounties[$killer_id][$bounty_type][1] = 0;
			}
		}
	}
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
					$bounties_in .= '"HQ",';
				}
				else {
					$bounties_in .= '"UG",';
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

function update_player(&$players,$account_id,&$hqs,&$ships) {
	global $db,$session;

	$query = 'UPDATE player SET credits=credits+' . $players[$account_id][CREDITS_GAINED] . ',experience=experience+' . $players[$account_id][EXPERIENCE_GAINED];
	
	if($players[$account_id][KILLER]) {
		// Escape pod speed is 7
		$turns = ceil($players[$account_id][TURNS] * (7 / $ships[$players[$account_id][SHIP_ID]][1]));
		if($turns > MAX_TURNS) {
			$turns = MAX_TURNS;
		}
		// Put them in a pod
		$query .= ',dead="TRUE",ship_type_id=69,newbie_turns=100,deaths=deaths+1,turns=';
		$query .= $turns;
		$query .= ',sector_id=';
		$query .= $hqs[$players[$account_id][RACE_ID]];
		$players[$account_id][SHIELDS] = 50;
		$players[$account_id][ARMOR] = 50;
		$players[$account_id][DRONES] = 0;
	}
		
	if(count($players[$account_id][KILLED]) > 0) {
		$query .= ',kills=kills+' . count($players[$account_id][KILLED]);
		$query .= ',alignment=alignment+' . $players[$account_id][ALIGNMENT_GAINED];
		$query .= ',military_payment=military_payment+' . $players[$account_id][MILITARY_GAINED];
	}
		
	$db->query('UPDATE ship_has_hardware SET amount=' . $players[$account_id][SHIELDS] . ' WHERE hardware_type_id=1 AND account_id=' . $account_id . ' AND game_id=' . SmrSession::$game_id . ' LIMIT 1');
	$db->query('UPDATE ship_has_hardware SET amount=' . $players[$account_id][ARMOR] . ' WHERE hardware_type_id=2 AND account_id=' . $account_id . ' AND game_id=' . SmrSession::$game_id . ' LIMIT 1');
	$db->query('UPDATE ship_has_hardware SET amount=' . $players[$account_id][DRONES] . ' WHERE hardware_type_id=4 AND account_id=' . $account_id . ' AND game_id=' . SmrSession::$game_id . ' LIMIT 1');
	
	$db->query($query . ' WHERE account_id=' . $account_id . ' AND game_id=' . SmrSession::$game_id . ' LIMIT 1');
}

function update_player_stats(&$players,$account_id,&$ships) {
	global $db,$session;
	
	$query = 'player_damage=player_damage+' . $players[$account_id][TOTAL_DAMAGE];
	
	if($players[$account_id][KILLER]) {
		$query .= ',deaths=deaths+1';
		$query .= ',turns_used=0';
		$query .= ',died_ships=died_ships+' . $ships[$players[$account_id][SHIP_ID]][0];
	}
	$num_kills = count($players[$account_id][KILLED]);
	if($num_kills) {
		$query .= ',kills=kills+' . $num_kills;
		$killed_xp = 0;
		$killed_ships = 0;
		for($i=0;$i<$num_kills;++$i){
			$traders_killed_exp += $players[$players[$account_id][KILLED][$i]][EXPERIENCE];
			$killed_ships += $ships[$players[$players[$account_id][KILLED][$i]][SHIP_ID]][0];
		}
		$query .= ',traders_killed_exp=traders_killed_exp+' . $traders_killed_exp;
		$query .= ',killed_ships=killed_ships+' . $killed_ships;
		// Weapon damage doesn't count towards kill xp. We must also adjust if the player themselves died.
		$query .= ',kill_exp=kill_exp+' . ($players[$account_id][EXPERIENCE_GAINED] + $players[$account_id][EXPERIENCE_LOST] - ceil($players[$account_id][TOTAL_DAMAGE] * 0.25));
		$query .= ',money_gained=money_gained+' . $players[$account_id][CREDITS_GAINED];
	 
	}

	$db->query('UPDATE player_has_stats SET ' . $query . ' WHERE account_id=' . $account_id . ' AND game_id=' . SmrSession::$game_id . ' LIMIT 1');
	$db->query('UPDATE account_has_stats SET ' . $query . ' WHERE account_id=' . $account_id . ' LIMIT 1');

}

// Initialise the required arrays
$attackers = array(); //built in players_init();
$defenders = array(); //built in players_init();
$players = players_init($attackers, $defenders);
$weapons = build_weapons(players_build_hardware($players));
$fleets = build_fleets($players,$weapons,$attackers,$defenders);

// Take off the 3 turns for attacking
$player->take_turns(3);
$player->update();

// Run the combat
for($i=0;$i<2;++$i) {
	process_fleet($fleets[$i],$fleets[1-$i],$players,$weapons);
}

// Build results and update
$killed_ids = array();
$killer_ids = array();

$results = build_results($players,$fleets,$weapons,$killed_ids,$killer_ids);

// TODO: Split this out into its own function
if(count($killed_ids)) {
	// These will get used for each alliances' kill/death results
	$fleets[0] = $fleets[1] = array(0,0);

	foreach($killed_ids as $account_id) {
		$ships[] = $players[$account_id][SHIP_ID];
		$hqs[] = $players[$account_id][RACE_ID];
		if($players[$account_id][ALLIANCE_ID] == $player->alliance_id) {
			++$fleets[0][1];
			++$fleets[1][0];
		}
		else {
			++$fleets[1][1];
			++$fleets[0][0];
		}
	}

	$ships = build_ships(array_unique($ships));
	$hqs = build_hqs(array_unique($hqs));
	$required_ids = array_merge($killed_ids,$killer_ids);

	foreach ($required_ids as $account_id) {
		$relations[] = $players[$account_id][RACE_ID];
	}

	$relations = build_relations(array_unique($relations));
	$ship_names = build_ship_names($required_ids);
	$bounties = build_bounties($required_ids);

	$messages = '';
	$unread_messages = '';
	$news_messages = '';

	foreach($killed_ids as $killed_id) {
		if(!empty($news)) {
			$news .= ',';
		}
		$news .= process_news($players,$killed_id,$ship_names);
		if(!empty($messages)) {
			$messages .= ',';
		}
		$messages .= process_messages($players,$killed_id);
	
		if(!empty($unread_messages)) {
			$unread_messages .= ',';
		}
		$unread_messages .= process_unread_messages($players,$killed_id);
	
		process_death($players,$killed_id,$ships,$relations);
		process_bounty($players,$killed_id,$bounties);
	}

	if(!empty($messages)) {
		$db->query('INSERT INTO player_has_unread_messages VALUES ' . $unread_messages);
		$db->query('INSERT INTO message (game_id,account_id,message_type_id,message_text,sender_id,send_time,msg_read,expire_time) VALUES ' . $messages);
		$db->query('INSERT INTO news (game_id,time,news_message,type) VALUES ' . $news);
	}

	unset($messages,$news,$unread_messages);

	update_bounties($bounties);
	update_podded($killed_ids);
	
	if($player->alliance_id) {
		$db->query('UPDATE alliance SET alliance_kills=alliance_kills+' . $fleets[0][0]. ',alliance_deaths=alliance_deaths+' .  $fleets[0][1] . ' WHERE alliance_id=' . $player->alliance_id . ' AND game_id=' . SmrSession::$game_id . ' LIMIT 1');
	}
	if($players[$var['target']][ALLIANCE_ID]) {
		$db->query('UPDATE alliance SET alliance_kills=alliance_kills+' . $fleets[1][0]. ',alliance_deaths=alliance_deaths+' .  $fleets[1][1] . ' WHERE alliance_id=' . $players[$var['target']][ALLIANCE_ID] . ' AND game_id=' . SmrSession::$game_id . ' LIMIT 1');
	}
	
	if($player->alliance_id && $players[$var['target']][ALLIANCE_ID]) {
		$db->query('SELECT kills FROM alliance_vs_alliance WHERE alliance_id_1=' . $player->alliance_id . ' AND alliance_id_2=' . $players[$var['target']][ALLIANCE_ID] . ' AND game_id=' . SmrSession::$game_id);
		if($db->next_record()) {
			$db->query('UPDATE alliance_vs_alliance SET kills=kills+' . $fleets[0][0] . ' WHERE alliance_id_1=' . $player->alliance_id . ' AND alliance_id_2=' . $players[$var['target']][ALLIANCE_ID] . ' AND game_id=' . SmrSession::$game_id . ' LIMIT 1');
		}
		else {
			$db->query('INSERT INTO alliance_vs_alliance VALUES (' . SmrSession::$game_id . ',' . $player->alliance_id . ',' . $players[$var['target']][ALLIANCE_ID] . ',' . $fleets[0][0] . ')');
		}
		
		$db->query('SELECT kills FROM alliance_vs_alliance WHERE alliance_id_1=' . $players[$var['target']][ALLIANCE_ID] . ' AND alliance_id_2=' . $player->alliance_id . ' AND game_id=' . SmrSession::$game_id);
		if($db->next_record()) {
			$db->query('UPDATE alliance_vs_alliance SET kills=kills+' . $fleets[1][0] . ' WHERE alliance_id_1=' . $players[$var['target']][ALLIANCE_ID] . ' AND alliance_id_2=' . $player->alliance_id . ' AND game_id=' . SmrSession::$game_id . ' LIMIT 1');
		}
		else {
			$db->query('INSERT INTO alliance_vs_alliance VALUES (' . SmrSession::$game_id . ',' . $players[$var['target']][ALLIANCE_ID] . ',' . $player->alliance_id . ',' . $fleets[1][0] . ')');
		}
	}
}

$account_ids = array_keys($players);

foreach($account_ids as $account_id) {
	update_player($players,$account_id,$hqs,$ships);
	update_player_stats($players,$account_id,$ships);
}

$db->query('UPDATE sector SET battles=battles+1 WHERE sector_id=' . $player->sector_id . ' AND game_id=' . SmrSession::$game_id . ' LIMIT 1');

$db->query('INSERT INTO combat_logs VALUES("",' . SmrSession::$game_id . ',"PLAYER",' . $player->sector_id . ',' . time() . ',' . SmrSession::$old_account_id . ',' . $player->alliance_id . ',' . $var['target'] . ',' . $players[$var['target']][ALLIANCE_ID] . ',"' . $db->escape_string(gzcompress($results)) . '", "FALSE")');

$container = array();
$container["url"] = "skeleton.php";
$container["body"] = "trader_attack_new.php";

// If their target is dead there is no continue attack button
if(!$players[$var["target"]][KILLER]) {
	$container["target"] = $var["target"];
}
else {
	$container["target"] = 0;
}

// If they died on the shot they get to see the results
if($players[$player->account_id][KILLER]) {
	$container['override_death'] = TRUE;
	$container["target"] = 0;
}

$container["results"] = $results;
forward($container);

?>
