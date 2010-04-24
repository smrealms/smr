<?php

define('NAP',0);
define('ASSIST',1);
define('DEFEND',2);

define ('PLAYER_ID', 0);
define ('PLAYER_NAME', 1);
define ('ALLIANCE_ID',2);
define ('RACE_ID', 3);
define ('SHIP_ID', 4);
define ('EXPERIENCE', 5);
define ('ALIGNMENT',6);
define ('LEVEL', 7);
define ('SHIELDS', 8);
define ('ARMOR', 9);
define ('DRONES', 10);
define ('WEAPONS', 11);
define ('SHIP_NAME',12);
define ('ATT_RATING',13);
define ('DEF_RATING',14);
define ('REAL_ATT',15);
define ('ILLEGALS',16);

if ($ship->hardware[HARDWARE_SCANNER] == 1)
	define('SCAN',1);
else
	define('SCAN',0);
// Get the player we're attacking
$db->query('SELECT ship_type.ship_name as ship_name,land_on_planet,newbie_turns,dead,sector_id,account_id,player_id,player_name,player.race_id as race_id,alignment,player.ship_type_id,experience,alliance_id,credits,turns
				FROM player, ship_type WHERE player.ship_type_id = ship_type.ship_type_id
				AND account_id=' . $var['target'] . '
				AND game_id=' . SmrSession::$game_id . ' LIMIT 1');
$db->next_record();
if($db->f('dead') == 'TRUE') {
	$container=array();
	$container['url'] = 'skeleton.php';
	$container['body'] = 'current_sector.php';
	$container['msg'] = '<span class="red bold">ERROR:</span> Target already dead.';
	forward($container);
}
$defenderNewb = ($db->f("newbie_turns"));
$players[$db->f('account_id')] = array(
	(int)$db->f('player_id'),
	get_colored_text($db->f('alignment'), stripslashes($db->f('player_name')) . ' (' . $db->f('player_id') . ')'),
	(int)$db->f('alliance_id'),
	(int)$db->f('race_id'),
	(int)$db->f('ship_type_id'),
	(int)$db->f('experience'),
	(int)$db->f('alignment'),0,0,0,0,array(),stripslashes($db->f("ship_name")),0,0,0,0
);
// Insert our own player into the players array
$players[SmrSession::$old_account_id] = array(
	(int)$player->player_id,
	get_colored_text($player->alignment,$player->player_name . ' (' . $player->player_id . ')'),
	(int)$player->alliance_id,
	(int)$player->race_id,
	(int)$player->ship_type_id,
	(int)$player->experience,
	(int)$player->alignment,0,0,0,0,array(),$ship->ship_name,0,0,0,0
);
$attackers = array();
$defenders = array();
if ($player->alliance_id)
	$attackers[] = $player->alliance_id;
if ($players[$var['target']][ALLIANCE_ID])
	$defenders[] = $players[$var['target']][ALLIANCE_ID];
	
// remove newbie gals
//// Get the galaxy name and id
//$db->query('SELECT galaxy_id FROM sector
//				WHERE sector_id=' . $player->sector_id . '
//				AND game_id=' . SmrSession::$game_id . '
//				LIMIT 1');
//
//$db->next_record();
//if($db->f('galaxy_id') < 9) {
//	$protection = TRUE;
//}
//else {
	$protection = FALSE;
//}
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
	player.alliance_id as alliance_id,
	ship_type.ship_name as ship_name
	FROM player, ship_type';

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
	$query .= ' AND ship_type.ship_type_id = player.ship_type_id';

	$db->query($query);

	while($db->next_record()) {
		$players[$db->f('account_id')] = array(
			(int)$db->f('player_id'),
			get_colored_text($db->f('alignment'),stripslashes($db->f('player_name')) . ' (' . $db->f('player_id') . ')'),
			(int)$db->f('alliance_id'),
			(int)$db->f('race_id'),
			(int)$db->f('ship_type_id'),
			(int)$db->f('experience'),
			(int)$db->f('alignment'),0,0,0,0,array(),stripslashes($db->f("ship_name")),0,0,0,0
		);
	}
}

// Figure out everyone's level
$db->query('SELECT level_id,requirement,level_name FROM level ORDER BY requirement DESC');
while($db->next_record()) {
	$levels[$db->f('level_id')] = $db->f('requirement');
	$levelNames[$db->f("level_id")] = $db->f("level_name");
}

$db->query("SELECT * FROM race");
while ($db->next_record()) $races[$db->f("race_id")] = stripslashes($db->f("race_name"));
$num_players = count($players);
$player_ids = array_keys($players);
$num_levels = count($levels);
$level_ids = array_keys($levels);
$db->query("SELECT account_id, tag FROM cpl_tag WHERE account_id IN (" . implode(',',$player_ids) . ") AND custom = 1 LIMIT $num_players");
while ($db->next_record()) $customTags[$db->f("account_id")] = $db->f("tag");
for($i=0;$i<$num_players;++$i) {
	for($j=0;$j<$num_levels;++$j) {
		if($levels[$level_ids[$j]] <= $players[$player_ids[$i]][EXPERIENCE]) {
			$players[$player_ids[$i]][LEVEL] = $level_ids[$j];
			break;
		}
	}
}
//get weapons
$db->query("SELECT account_id, ship_has_weapon.weapon_type_id as id, shield_damage, armor_damage
			FROM ship_has_weapon, weapon_type
			WHERE account_id IN (" . implode(',',$player_ids) . ")
			AND game_id = $player->game_id
			AND weapon_type.weapon_type_id = ship_has_weapon.weapon_type_id
			ORDER BY account_id, order_id");
while ($db->next_record()) {
	$weapons[$db->f("id")] = array($db->f("shield_damage"), $db->f("armor_damage"));
	$players[$db->f("account_id")][WEAPONS][] = $db->f("id");
}
//get hardware
$hardwareWeCareAbout = "(1,2,4)";
$db->query("SELECT * FROM ship_has_hardware
			WHERE hardware_type_id IN $hardwareWeCareAbout
			AND account_id IN (" . implode(',',$player_ids) . ")
			AND game_id = $player->game_id");
while ($db->next_record()) {
	switch($db->f("hardware_type_id")) {
		case (1):
			$players[$db->f("account_id")][SHIELDS] = $db->f("amount");
			break;
		case (2):
			$players[$db->f("account_id")][ARMOR] = $db->f("amount");
			break;
		case (4):
			$players[$db->f("account_id")][DRONES] = $db->f("amount");
			break;
		default:
			break;
	}
}
$db->query("SELECT ship_name, account_id, attack, defense FROM ship_has_illusion, ship_type
			WHERE game_id = $player->game_id
			AND account_id IN (" . implode(',',$player_ids) . ")
			AND ship_type.ship_type_id = ship_has_illusion.ship_type_id");
while ($db->next_record()) {
	$players[$db->f("account_id")][SHIP_NAME] = stripslashes($db->f("ship_name"));
	$players[$db->f("account_id")][ATT_RATING] = $db->f("attack");
	$players[$db->f("account_id")][DEF_RATING] = $db->f("defense");
}
$attSize = sizeof($attackers);
$defSize = sizeof($defenders);
$limit = $attSize + $defSize;
$query = 'SELECT alliance_id, alliance_name FROM alliance
				WHERE game_id = ' . $player->game_id;
if ($attSize && $defSize)
	$query .= ' AND (alliance_id IN (' . implode(',',$attackers) . ') OR alliance_id IN (' . implode(',',$defenders) . '))';
elseif ($attSize)
	$query .= ' AND alliance_id IN (' . implode(',',$attackers) . ')';
elseif ($defSize)
	$query .= ' AND alliance_id IN (' . implode(',',$defenders) . ')';
else $query .= ' AND alliance_id = 0';
$query .= ' LIMIT ' . $limit;
$db->query($query);
while ($db->next_record()) $alliances[$db->f("alliance_id")] = stripslashes($db->f("alliance_name"));
$db->query("SELECT * FROM location WHERE location_type_id = ".FED." AND sector_id = $player->sector_id AND game_id = $player->game_id LIMIT 1");
if ($db->next_record()) $fedBeacon = TRUE;
else $fedBeacon = FALSE;
$attackingFleet = array();
$defendingFleet = array();
if (!$attSize) $attackingFleet[] = $player->account_id;
if (!$defSize) $defendingFleet[] = $var['target'];
$db->query('SELECT account_id FROM ship_has_cargo WHERE good_id IN (5,9,12) AND game_id=' . SmrSession::$game_id . ' AND account_id IN (' . implode(',',$player_ids) . ') LIMIT ' . sizeof($player_ids));
while ($db->next_record())
	$players[$db->f('account_id')][ILLEGALS] = 1;
foreach ($players as $accID => $playerArray) {
	//get attack/def ratings
	$playerDMG = 0;
	foreach ($playerArray[WEAPONS] as $wepID) $playerDMG += $weapons[$wepID][0] + $weapons[$wepID][1];
	$maxDronesPercent = (35 + $playerArray[LEVEL] * .6 + ($playerArray[LEVEL] - 1) * .4 + 15) * .01;
	$maxDrones = $maxDronesPercent * $playerArray[DRONES];
	$attack_rating = round((($playerDMG + $maxDrones * 2) / 40));
	if (!$players[$accID][ATT_RATING]) $players[$accID][ATT_RATING] = $attack_rating;
	$players[$accID][REAL_ATT] = $attack_rating;
	if (!$players[$accID][DEF_RATING]) $players[$accID][DEF_RATING] = round( ($playerArray[SHIELDS] + $playerArray[ARMOR]) / 100 + $playerArray[DRONES] * 3 / 100 );
	//get fleets
	if (playerFedCheck($players[$accID], $fedBeacon) && $accID != $player->account_id) continue;
	if (in_array($playerArray[ALLIANCE_ID], $attackers)) $attackingFleet[] = $accID;
	elseif (in_array($playerArray[ALLIANCE_ID], $defenders)) $defendingFleet[] = $accID;
}
if (in_array($players[$var['target']][ALLIANCE_ID],$attackers)) {
	if (!in_array($var['target'], $attackingFleet)) $attackingFleet[] = $var['target'];
	$defendingFleet = array();
}

$allied = (isset($treaties_attacker[NAP][$players[$var['target']][ALLIANCE_ID]]));
$defenderFed = playerFedCheck($players[$var['target']], $fedBeacon);
$playerCanShoot = (sizeof($players[$player->account_id][WEAPONS]) > 0 || $players[$player->account_id][DRONES] > 0);
$playerFed = playerFedCheck($players[$player->account_id], $fedBeacon);
$playerNewb = ($player->newbie_turns);
$allowedByAlliance = (!$player->alliance_id || !$players[$var['target']][ALLIANCE_ID] || $players[$var['target']][ALLIANCE_ID] != $player->alliance_id);
print_topic("EXAMINE SHIP");
// should we display a attack button
if ($playerCanShoot && !$playerFed && !$defenderFed && !$playerNewb && !$defenderNewb && $allowedByAlliance && !$allied) {
	$container = create_container('skeleton.php','trader_attack_processing_new.php');
	transfer("target");
	print_form($container);
	print_submit("Attack Trader (3)");
	print("</form><br />");
} else {
	echo '<p><big class="';
	if ($allied) echo 'blue">This is your ally.';
	elseif ($playerFed) echo 'blue">You are under federal protection! That wouldn\'t be fair.';
	elseif ($defenderFed) echo 'blue">Your target is under federal protection!';
	elseif ($playerNewb) echo 'green">You are under newbie protection!';
	elseif ($defenderNewb) echo 'green">Your target is under newbie protection!';
	elseif (!$allowedByAlliance) echo 'blue">This is your alliancemate.';
	elseif (!$playerCanShoot) echo 'red">You ready your weapons, you take aim, you...realize you have no weapons.';
	else echo 'red">Uhhhh, something is wrong.  Screenshot and tell Azool please.';
	echo '</big></p>';
}

echo '<div align="center">';
echo '<table cellspacing="0" cellpadding="5" border="0" class="standard" width="95%">';
echo '<tr><th width="50%">Attacker</th><th width="50%">Defender</th></tr>';
print("<tr>");
for ($i=0;$i<=1;$i++) {
	echo '<td style="vertical-align:top;">';
	$fleet = $attackingFleet;
	if ($i) {	
		$fleet = $defendingFleet;
		if ($player->newbie_turns || $playerFed || !sizeof($fleet)) {
			print("&nbsp;");
			$fleet = array();
		}		
	}
	foreach ($fleet as $accID) {
		if (isset($customTags[$accID])) print($customTags[$accID]);
		else print($levelNames[$players[$accID][LEVEL]]);
		print("<br />");
		print($players[$accID][PLAYER_NAME] . "<br />");
		print("Race: " . $races[$players[$accID][RACE_ID]] . "<br .>");
		print("Level: " . $players[$accID][LEVEL] . "<br />");
		print("Alliance: " . $alliances[$players[$accID][ALLIANCE_ID]] . "<br /><br />");
		print("<small>" . $players[$accID][SHIP_NAME] . "<br />");
		print("Rating : " . $players[$accID][ATT_RATING] . "/" . $players[$accID][DEF_RATING] . "<br />");
		if (SCAN) {
			print("Shields : " . (floor($players[$accID][SHIELDS] / 100) * 100) . "-" . (floor($players[$accID][SHIELDS] / 100 + 1) * 100) . "<br />");
			print("Armor : " . (floor($players[$accID][ARMOR] / 100) * 100) . "-" . (floor($players[$accID][ARMOR] / 100 + 1) * 100) . "<br />");
			print("Hard Points: " . sizeof($players[$accID][WEAPONS]) . "<br />");
			print("Combat Drones: " . (floor($players[$accID][DRONES] / 100) * 100) . "-" . (floor($players[$accID][DRONES] / 100 + 1) * 100) . "<br />");
		}
		print("</small><br /><br />");
	}
	print("</td>");
}
print("</tr></table></div>");
function playerFedCheck($playerArray, $fedBeacon) {
	if (!$fedBeacon) return FALSE;
	if ($playerArray[ILLEGALS]) return FALSE;
	if (!$playerArray[REAL_ATT]) return TRUE;
	if ($playerArray[ALIGNMENT] > 0)
		$alignMod = floor($playerArray[ALIGNMENT] / 150);
	else
		$alignMod = ceil($playerArray[ALIGNMENT] / 150);
	$alignMod += 3;
	if ($alignMod > 8) $alignMod = 8;
	return ($playerArray[REAL_ATT] <= $alignMod);
}
?>