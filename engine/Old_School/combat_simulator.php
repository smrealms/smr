<?php
$smarty->assign('PageTopic','Combat Simulator');

define('ITERATIONS', 250);
// TODO: Cleanup and finalise
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

define ('WEAPON_NAME', 0);
define ('SHIELD_DAMAGE', 1);
define ('ARMOR_DAMAGE', 2);
define ('ACCURACY', 3);

define('MESSAGE_EXPIRES', TIME + 259200);

//treaty stuff
define('NAP',0);
define('DEFEND',1);
define('ASSIST',2);

require_once(get_file_loc('DummyPlayer.class.inc'));

$smarty->assign('EditDummysLink',SmrSession::get_new_href(create_container('skeleton.php','edit_dummys.php')));
$smarty->assign('DummyNames', DummyPlayer::getDummyPlayerNames());

$attackers = array();
if(isset($_POST['attackers']))
	foreach($_POST['attackers'] as $orderID => $attackerName)
	{
		$attackers[$orderID] =& DummyPlayer::getCachedDummyPlayer($attackerName);
		$attackers[$orderID]->setAllianceID(1);
	}
	
$defenders = array();
if(isset($_POST['defenders']))
	foreach($_POST['defenders'] as $orderID => $defenderName)
	{
		$defenders[$orderID] =& DummyPlayer::getCachedDummyPlayer($defenderName);
		$defenders[$orderID]->setAllianceID(2);
	}

if(isset($_POST['action']) && $_POST['action'] == 1) 
{
	$store[1][0] = $_POST['level'];
	$store[1][1] = $_POST['ship_id'];
	if(isset($_POST['DCS'])) {
		$store[1][2] = 1;
	}
	else {
		$store[1][2] = 0;	
	}
	
	// Grab weapons;
	for($i=0;$i<8;++$i) {
		if($i >= $ships[$store[1][1]][1]) {
			unset($store[1][3][$i]);
		}
		else if(isset($_POST['weapon_' . $i])) {
			$store[1][3][$i] = (int)$_POST['weapon_' . $i];
		}	
	}
}



//
//
//require_once(LIB . 'global/smr_db.inc');
//
//$db = new SMR_DB();
//
//
//$db->query('SELECT ship_type_id,ship_name,hardpoint FROM ship_type WHERE ship_type_id!=68 AND ship_type_id!=999 ORDER BY ship_type_id');
//while($db->next_record()) {
//	$ships[$db->f('ship_type_id')] = array($db->f('ship_name'),$db->f('hardpoint'));
//}
//
//$db->query('SELECT weapon_type_id, weapon_name, shield_damage, armor_damage, accuracy, power_level FROM weapon_type WHERE weapon_type_id < 10000 ORDER BY weapon_type_id');
//while($db->next_record()) {
//	$weapons[$db->f('weapon_type_id')] = array($db->f('weapon_name'),$db->f('shield_damage'),$db->f('armor_damage'),$db->f('accuracy'),$db->f('power_level'));
//}
//
//if(isset($_POST['action']) && $_POST['action'] == 1) {
//	$store = unserialize(base64_decode($_POST['stored']));
//	$store[1][0] = $_POST['level'];
//	$store[1][1] = $_POST['ship_id'];
//	if(isset($_POST['DCS'])) {
//		$store[1][2] = 1;
//	}
//	else {
//		$store[1][2] = 0;	
//	}
//	
//	// Grab weapons;
//	for($i=0;$i<8;++$i) {
//		if($i >= $ships[$store[1][1]][1]) {
//			unset($store[1][3][$i]);
//		}
//		else if(isset($_POST['weapon_' . $i])) {
//			$store[1][3][$i] = (int)$_POST['weapon_' . $i];
//		}	
//	}
//}
//else if(isset($_POST['action']) && $_POST['action'] == 2) {
//	$store = unserialize(base64_decode($_POST['stored']));
//	$store[2][0] = $_POST['level'];
//	$store[2][1] = $_POST['ship_id'];
//	if(isset($_POST['DCS'])) {
//		$store[2][2] = 1;
//	}
//	else {
//		$store[2][2] = 0;	
//	}
//	// Grab weapons;
//	for($i=0;$i<8;++$i) {
//		if($i >= $ships[$store[2][1]][1]) {
//			unset($store[2][3][$i]);
//		}
//		else if(isset($_POST['weapon_' . $i])) {
//			$store[2][3][$i] = (int)$_POST['weapon_' . $i];
//		}	
//	}
//	
//}
//else if(isset($_POST['action']) && $_POST['action'] == 3) {
//	$store = unserialize(base64_decode($_POST['stored']));
//}
//
//else {
//	$store = array( 
//		1 => array(0,1,0,array(1)),
//		2 => array(0,1,0,array(1))
//	);
//}
//
//echo '<html><head></head><body>';
//
//echo '<table><tr><td style="vertical-align:top">';
//echo '<u>Player One</u><br/><br />';
//echo '<form action="' . $_SERVER['REQUEST_URI'] . '" method="POST">';
//echo '<input type="hidden" name="action" value="1" />';
//echo '<input type="hidden" name="stored" value="' . base64_encode(serialize($store)) . '" />';
//echo 'Level:&nbsp;';
//echo '<select name="level">';
//for($i=0;$i<51;++$i) {
//	echo '<option value="' . $i . '"';
//	if($i == $store[1][0]) {
//		echo ' selected="selected"';
//	}
//	echo '>' . $i . '</option>';
//}
//
//echo '</select>';
//echo '&nbsp;Ship:&nbsp;';
//echo '<select name="ship_id">';
//
//foreach ($ships as $id => $ship) {
//	echo '<option value="' . $id . '"';
//	if($id == $store[1][1]) {
//		echo ' selected="selected"';
//	}
//	echo '>' . $ship[0] . '</option>';
//}
//
//echo '</select>';
//echo '&nbsp;DCS&nbsp;<input type="checkbox" ';
//if($store[1][2]) {
//	echo 'checked="checked" ';
//}
//echo 'name="DCS" />';
//echo '<input type="submit" value="Alter Player One" />';
//echo '<br /><br />';
//
//// Weapons
//for($i=0;$i<$ships[$store[1][1]][1];++$i) {
//	echo 'Weapon: ' . ($i+1) . '&nbsp;';
//	echo '<select name="weapon_' . $i . '">';
//	foreach($weapons as $id => $weapon) {
//		echo '<option value="' . $id . '"';
//		if($store[1][3][$i] == $id) {
//			echo ' selected="selected"';
//		} 
//		echo '>' . $weapon[0] . ' (dmg: ' . $weapon[1] . '/' . $weapon[2] . ' acc: ' . $weapon[3] . '% lvl:' . $weapon[4] . ')</option>';
//	}
//	echo '</select>';
//	echo '<br />';
//}
//
//echo '</form>';
//echo '</td><td style="vertical-align:top"><u>Current Details</u><br/><br/>';
//
//echo 'Level: ' . $store[1][0] . '<br />';
//echo 'Ship: ' . $ships[$store[1][1]][0] . '<br />';
//echo 'DCS: ';
//if($store[1][2]) {
//	echo 'True';
//}
//else {
//	echo 'false';
//}
//echo '<br/>Weapons:<br/>';
//foreach($store[1][3] as $weapon_id) {
//	echo $weapons[$weapon_id][0] . '<br />';
//}
//	
//echo '</td></tr><tr><td style="vertical-align:top">';
//
//echo '<u>Player Two</u><br/><br />';
//echo '<form action="' . $_SERVER['REQUEST_URI'] . '" method="POST">';
//echo '<input type="hidden" name="action" value="2" />';
//echo '<input type="hidden" name="stored" value="' . base64_encode(serialize($store)) . '" />';
//echo 'Level:&nbsp;';
//echo '<select name="level">';
//for($i=0;$i<51;++$i) {
//	echo '<option value="' . $i . '"';
//	if($i == $store[2][0]) {
//		echo ' selected="selected"';
//	}
//	echo '>' . $i . '</option>';
//}
//
//echo '</select>';
//echo '&nbsp;Ship:&nbsp;';
//echo '<select name="ship_id">';
//
//foreach ($ships as $id => $ship) {
//	echo '<option value="' . $id . '"';
//	if($id == $store[2][1]) {
//		echo ' selected="selected"';
//	}
//	echo '>' . $ship[0] . '</option>';
//}
//
//echo '</select>';
//echo '&nbsp;DCS&nbsp;<input type="checkbox" ';
//if($store[2][2]) {
//	echo 'checked="checked" ';
//}
//echo 'name="DCS" />';
//echo '<input type="submit" value="Alter Player Two" />';
//echo '<br /><br />';
//
//// Weapons
//for($i=0;$i<$ships[$store[2][1]][1];++$i) {
//	echo 'Weapon: ' . ($i+1) . '&nbsp;';
//	echo '<select name="weapon_' . $i . '">';
//	foreach($weapons as $id => $weapon) {
//		echo '<option value="' . $id . '"';
//		if($store[2][3][$i] == $id) {
//			echo ' selected="selected"';
//		} 
//		echo '>' . $weapon[0] . ' (dmg: ' . $weapon[1] . '/' . $weapon[2] . ' acc: ' . $weapon[3] . '% lvl:' . $weapon[4] . ')</option>';
//	}
//	echo '</select>';
//	echo '<br />';
//}
//echo '</form>';
//echo '</td><td style="vertical-align:top"><u>Current Details</u><br/><br/>';
//
//echo 'Level: ' . $store[2][0] . '<br />';
//echo 'Ship: ' . $ships[$store[2][1]][0] . '<br />';
//echo 'DCS: ';
//if($store[2][2]) {
//	echo 'True';
//}
//else {
//	echo 'false';
//}
//echo '<br/>Weapons:<br/>';
//foreach($store[2][3] as $weapon_id) {
//	echo $weapons[$weapon_id][0] . '<br />';
//}
//	
//	
//
//echo '</td></tr><tr><td colspan = "2" style="text-align:center">';
//
//echo '<br />All drones, shields, armour assumed full at the start of the simulation<br/><br />';
//
//echo '</form>';
//echo '<form action="' . $_SERVER['REQUEST_URI'] . '" method="POST">';
//echo '<input type="hidden" name="action" value="3" />';
//echo '<input type="hidden" name="stored" value="' . base64_encode(serialize($store)) . '" />';
//echo '<input type="submit" value="Run Simulation" />';
//echo '</form>';
//
//echo '</td></tr>';
//
//if(isset($_POST['action']) && $_POST['action'] == 3) {
//	echo '<tr><td colspan="2">';
//	// Insert our own player into the players array
//	$players[1] = array(
//		(int)1,
//		'Player One',
//		(int)1,
//		(int)0,
//		(int)0,
//		(int)0,
//		(int)0,
//		(int)$store[1][1],
//		(int)0,
//		(int)$store[1][0],
//		0,
//		0,
//		0,
//		0,
//		$store[1][2],$store[1][3],array(),0,0,0,array(),0,0,0
//	);
//		
//	// Insert our own player into the players array
//	$players[2] = array(
//		(int)2,
//		'Player Two',
//		(int)2,
//		(int)0,
//		(int)0,
//		(int)0,
//		(int)0,
//		(int)$store[2][1],
//		(int)0,
//		(int)$store[2][0],
//		0,
//		0,
//		0,
//		0,
//		$store[2][2],$store[2][3],array(),0,0,0,array(),0,0,0
//	);
//	
//	
//	if(count($players[2][WEAPONS]) + count($players[1][WEAPONS]) == 0) {
//		echo 'No. I can\'t be arsed to code in checks for nobody having weapons.';
//		exit;
//	}
//	
//	$db->query('SELECT hardware_type_id,max_amount FROM ship_type_support_hardware WHERE ship_type_id=' . $store[1][1] . ' AND (hardware_type_id=' . HARDWARE_SHIELDS . ' OR hardware_type_id=' . HARDWARE_ARMOR . ' OR hardware_type_id=' . HARDWARE_COMBAT . ')');
//	
//	while($db->next_record()) {
//		switch($db->f('hardware_type_id')) {
//		case(HARDWARE_SHIELDS):
//			$players[1][SHIELDS] = (int)$db->f('max_amount');
//			break;
//		case(HARDWARE_ARMOR):
//			$players[1][ARMOR] = (int)$db->f('max_amount');
//			break;
//		case(HARDWARE_COMBAT):
//			$players[1][DRONES] = (int)$db->f('max_amount');
//			// They fire the same amount of drones they start the round with
//			$players[1][DRONES_ORIGINAL] = (int)$db->f('max_amount');
//			// Drones count as a weapon. It's important they are last in the order
//			if($db->f('max_amount')) {
//				$players[1][WEAPONS][] = 0;
//			}
//			break;
//		}
//	}
//	
//	$db->query('SELECT hardware_type_id,max_amount FROM ship_type_support_hardware WHERE ship_type_id=' . $store[2][1] . ' AND (hardware_type_id=' . HARDWARE_SHIELDS . ' OR hardware_type_id=' . HARDWARE_ARMOR . ' OR hardware_type_id=' . HARDWARE_COMBAT . ')');
//	
//	while($db->next_record()) {
//		switch($db->f('hardware_type_id')) {
//		case(HARDWARE_SHIELDS):
//			$players[2][SHIELDS] = (int)$db->f('max_amount');
//			break;
//		case(HARDWARE_ARMOR):
//			$players[2][ARMOR] = (int)$db->f('max_amount');
//			break;
//		case(HARDWARE_COMBAT):
//			$players[2][DRONES] = (int)$db->f('max_amount');
//			// They fire the same amount of drones they start the round with
//			$players[2][DRONES_ORIGINAL] = (int)$db->f('max_amount');
//			// Drones count as a weapon. It's important they are last in the order
//			if($db->f('max_amount')) {
//				$players[2][WEAPONS][] = 0;
//			}
//			break;
//		}
//	}
//
//	mysql_close($db->Link_ID);
//	
//	$fleets = array(
//		0 => array(1),
//		1 => array(2)
//	);
//
//	$orig_players = $players;
//	
//	// Run the combat
//
//
//  $att_win = 0;
//  $def_win = 0;
//  $no_win = 0;
//  $num_rounds = 0;
//
//  $NEW_DRONE_EQUATION = false;
//  $TEST_DRONE_EQUATION = false;
//  $NEW_ACCURACY = false;
//  $NEW_DRONE = false;
//  $NEW_DRONE_DAMAGE = false;
//  
//  for($j=0;$j<ITERATIONS;$j++) {
//    $players = $orig_players;
//  
//    while($players[1][ARMOR] > 0 && $players[2][ARMOR] > 0) {
//      for($i=0;$i<2;++$i) {
//        process_fleet($fleets[$i],$fleets[1-$i],$players,$weapons);
//      }
//      ++$num_rounds;
//      $players[1][DRONES_ORIGINAL] = $players[1][DRONES];
//      $players[2][DRONES_ORIGINAL] = $players[2][DRONES];
//      
//    }
//    
//    if($players[1][ARMOR] == 0 && $players[2][ARMOR] == 0) {
//      ++$no_win;
//    }
//    else if($players[1][ARMOR] == 0) {
//      ++$def_win;
//    }
//    else {
//      ++$att_win;
//    }
//  }
//  $num_rounds = ($num_rounds/ITERATIONS);
//  
//  echo '<u>Old Combat Code, drones from start of attack</u><br/>Attacker: ' . $att_win . ' (' . number_format(100*($att_win/ITERATIONS),2) . '%) Defender: ' . $def_win . ' (' . number_format(100*($def_win/ITERATIONS),2) . '%)  Tie: ' . $no_win . ' (' . number_format(100*($no_win/ITERATIONS),2) . '%)  Rounds: ' . number_format($num_rounds,2) . '<br /><br />';
//
//	$att_win = 0;
//	$def_win = 0;
//	$no_win = 0;
//	$num_rounds = 0;
//
//	$NEW_DRONE_EQUATION = false;
//	$TEST_DRONE_EQUATION = true;
//	$NEW_ACCURACY = 2;
//	$NEW_DRONE = true;
//	$NEW_DRONE_DAMAGE = false;
//	
//	for($j=0;$j<ITERATIONS;$j++) {
//		$players = $orig_players;
//	
//		while($players[1][ARMOR] > 0 && $players[2][ARMOR] > 0) {
//			for($i=0;$i<2;++$i) {
//				process_fleet($fleets[$i],$fleets[1-$i],$players,$weapons);
//			}
//			++$num_rounds;
//            $players[1][DRONES_ORIGINAL] = $players[1][DRONES];
//      $players[2][DRONES_ORIGINAL] = $players[2][DRONES];
//		}
//		
//		if($players[1][ARMOR] == 0 && $players[2][ARMOR] == 0) {
//			++$no_win;
//		}
//		else if($players[1][ARMOR] == 0) {
//			++$def_win;
//		}
//		else {
//			++$att_win;
//		}
//	}
//	$num_rounds = ($num_rounds/ITERATIONS);
//	echo '<u>Current Round Equations</u><br/>Attacker: ' . $att_win . ' (' . number_format(100*($att_win/ITERATIONS),2) . '%) Defender: ' . $def_win . ' (' . number_format(100*($def_win/ITERATIONS),2) . '%)  Tie: ' . $no_win . ' (' . number_format(100*($no_win/ITERATIONS),2) . '%)  Rounds: ' . number_format($num_rounds,2) . '<br /><br />';
//
//	
//
//
//  $att_win = 0;
//  $def_win = 0;
//  $no_win = 0;
//  $num_rounds = 0;
//
//  $NEW_DRONE_EQUATION = false;
//  $TEST_DRONE_EQUATION = false;
//  $NEW_ACCURACY = false;
//  $NEW_DRONE = true;
//  $NEW_DRONE_DAMAGE = false;
//  
//  for($j=0;$j<ITERATIONS;$j++) {
//    $players = $orig_players;
//  
//    while($players[1][ARMOR] > 0 && $players[2][ARMOR] > 0) {
//      for($i=0;$i<2;++$i) {
//        process_fleet($fleets[$i],$fleets[1-$i],$players,$weapons);
//      }
//      ++$num_rounds;
//      $players[1][DRONES_ORIGINAL] = $players[1][DRONES];
//      $players[2][DRONES_ORIGINAL] = $players[2][DRONES];
//      
//    }
//    
//    if($players[1][ARMOR] == 0 && $players[2][ARMOR] == 0) {
//      ++$no_win;
//    }
//    else if($players[1][ARMOR] == 0) {
//      ++$def_win;
//    }
//    else {
//      ++$att_win;
//    }
//  }
//  $num_rounds = ($num_rounds/ITERATIONS);
//  
//  echo '<u>Old Drone Equation, drones from start of round</u><br/>Attacker: ' . $att_win . ' (' . number_format(100*($att_win/ITERATIONS),2) . '%) Defender: ' . $def_win . ' (' . number_format(100*($def_win/ITERATIONS),2) . '%)  Tie: ' . $no_win . ' (' . number_format(100*($no_win/ITERATIONS),2) . '%)  Rounds: ' . number_format($num_rounds,2) . '<br /><br />';
//
//	
//}
//  echo '</td></tr>';
//echo '</table></body>';
//echo '</html>';
//
//
//function process_fleet(&$attackers,&$defenders,&$players,&$weapons) {
//	$fleet_size = count($attackers);
//	// Process each player in turn
//	for($i=0;$i<$fleet_size;++$i) {
//		process_attacker($attackers[$i],$defenders,$players,$weapons);
//	}
//}
//
//function process_attacker($attacker,&$defenders,&$players,&$weapons) {
//	$num_weapons = count($players[$attacker][WEAPONS]);
//	// Process each weapon in turn
//	for($i=0;$i<$num_weapons;++$i) {
//		// Select a random defender
//		$defender = $defenders[array_rand($defenders)];
//		$result = process_weapon($players[$attacker][WEAPONS][$i],$attacker,$defender,$players,$weapons);
//
//		// Take the appropriate damage from the defender
//		$players[$defender][SHIELDS] -= $result[0];
//		$players[$defender][DRONES] -= floor($result[1]/3);
//		$players[$defender][ARMOR] -= $result[2];
//
//		$result[5] = $defender;
//
//		$players[$attacker][RESULTS][] = $result;
//
//		// Did they kill somebody?
//		if($result[4] == FINAL_HIT) {
//			// Record this for news and messages later
//			$players[$defender][KILLER] = $attacker;
//			$players[$attacker][KILLED][] = $defender;
//		}
//	}
//}
//
//function process_weapon($weapon,$attacker,$defender,&$players,&$weapons) {
//	global $NEW_DRONE_EQUATION, $NEW_ACCURACY, $NEW_DRONE, $NEW_DRONE_DAMAGE,$TEST_DRONE_EQUATION;
//	$result = array(0,0,0,0,NORMAL_HIT);
//
//	// Does the weapon hit?
//	if($weapon) {
//		$hit = $weapons[$weapon][ACCURACY] + 
//			($players[$attacker][LEVEL] - ($players[$defender][LEVEL] * 0.5));
//
//		// Non-linear distribution to highlight lvl differences
//		if($NEW_ACCURACY) {
//		  if($NEW_ACCURACY === 2) {
//
//			$rand = (mt_rand(0,99) + (0.15*mt_rand(0,99))) / 1.15;
//          }
//          else {
//              $rand = (mt_rand(0,99) + (0.38*mt_rand(0,99))) / 1.38;   
//          }
//		}
//		else {
//			$rand = mt_rand(0,99);
//		}
//		
//		// TODO:$rand = (mt_rand(0,100) + mt_rand(0,100)) * 0.5;
//
//		if($rand > $hit) {
//			$result[4] = WEAPON_MISS;
//			return $result;
//		}
//	}
//
//	// Drones are weapon id 0 and their damage rolls over
//	if(!$weapon) {
//		// Calculate how many drones actual fire
//
//		// New hotness
//		if($NEW_DRONE_EQUATION) {
//			$drones_percentage = (($players[$attacker][LEVEL] * 0.3) + mt_rand(0, ( ($players[$attacker][LEVEL] - $players[$defender][LEVEL] ) * 0.7)) + mt_rand(35,50)) * 0.01;
//		}
//		else if($TEST_DRONE_EQUATION) {
//			//$drones_percentage = (($players[$attacker][LEVEL] - $players[$defender][LEVEL] + 25) + mt_rand((($players[$attacker][DRONES_ORIGINAL] * 0.05)-25),25)) * 0.01;
//			$drones_percentage = ((0.05 * $players[$attacker][DRONES_ORIGINAL]) + $players[$attacker][LEVEL] - ($players[$defender][LEVEL]*0.5) + ((mt_rand(-30,30) + 0.38*mt_rand(-30,30))/1.38)) * 0.01;
//			//echo $drones_percentage;
//
//		}
//		else {
//          $drones_percentage = ((mt_rand(3,54)+mt_rand($players[$attacker][LEVEL]/4,$players[$attacker][LEVEL]))-($players[$defender][LEVEL]-$players[$attacker][LEVEL])/3)/100;
//		}
//
//		if($drones_percentage < 0) $drones_percentage = 0;
//		else if($drones_percentage > 1) $drones_percentage = 1;
//
//		if($NEW_DRONE) {
//			$result[3] = ceil($players[$attacker][DRONES_ORIGINAL] * $drones_percentage);
//		}
//		else {
//			$result[3] = ceil($players[$attacker][DRONES] * $drones_percentage);
//      
//		}
//
//		if(!$NEW_DRONE_DAMAGE) {
//			if(!$players[$defender][DCS]) {
//				$potential_damage = floor(2.0 * $result[3]);
//
//			}
//			else {
//				// Drones only do 1.5 damage against DCS carrying players
//				$potential_damage = floor(1.5 * $result[3]);
//			}
//		}
//		else {
//			if(!$players[$defender][DCS]) {
//				$potential_damage = floor(1.6 * $result[3]);
//			}
//			else {
//				// Drones only do 1.5 damage against DCS carrying players
//				$potential_damage = floor(1.2 * $result[3]);
//			}
//		}
//			
//
//		// Yes, they can miss with all drones
//		if(!$potential_damage) {
//			$result[4] = WEAPON_MISS;
//			return $result;
//		}
//	}
//	else {
//		$potential_damage = $weapons[$weapon][SHIELD_DAMAGE];
//	}
//
//	// Are they already dead?
//	if($players[$defender][ARMOR] == 0 ) {
//		$result[4] = HIT_DEBRIS;
//		return $result;
//	}
//
//	// Try to hit shields
//	if($players[$defender][SHIELDS] != 0 ) {
//		// Does the weapon do shield damage?
//		if($potential_damage) {
//			// Have we produced more damage than there are shields remaining?
//			if($potential_damage >= $players[$defender][SHIELDS]) {
//				$result[0] =  $players[$defender][SHIELDS];
//			}
//			else {
//				$result[0] = $potential_damage;
//			}
//
//			// If it's an ordinary weapon or drones are out of damage then return
//			if($weapon || $result[0] == $potential_damage) {
//				$result[4] = NORMAL_HIT;
//				return $result;
//			}
//		}
//		else {
//			$result[4] = ARMOR_ON_SHIELD;
//			return $result;
//		}
//	}
//
//	// If a drone shot then adjust damage so we work in units of 1 drone
//	if(!$weapon) {
//		$potential_damage -= $result[0];
//		if(!$players[$defender][DCS]) {
//			$potential_damage = 2 * floor($potential_damage/2);
//		}
//		else {
//			// DCS reduces damage by 75% (We can't take off 0.5 of anything)
//			$potential_damage = floor(1.5 * floor($potential_damage/1.5));
//		}
//		if($potential_damage == 0) {
//			$result[4] = NORMAL_HIT;
//			return $result;
//		}
//	}
//	else {
//		$potential_damage = $weapons[$weapon][ARMOR_DAMAGE];
//	}
//
//	// No shields left, try to hit their drones
//	if($players[$defender][DRONES] != 0 ) {
//		// Does the weapon do armor damage?
//		if($potential_damage) {
//			// Have we produced more damage than there are shields remaining?
//			if($potential_damage >= $players[$defender][DRONES] * 3) {
//				$result[1] =  $players[$defender][DRONES] * 3;
//			}
//			else {
//				$result[1] = $potential_damage;
//			}
//			// If it's an ordinary weapon or drones are out of damage then return
//			if($weapon || $result[1] == $potential_damage) {
//				$result[4] = NORMAL_HIT;
//				return $result;
//			}
//		}
//		else {
//			$result[4] = SHIELD_ON_DRONES;
//			return $result;
//		}
//	}
//
//	// If a drone shot then adjust damage so we work in units of 1 drone
//	if(!$weapon) {
//		$potential_damage -= $result[1];
//		if(!$players[$defender][DCS]) {
//			$potential_damage = 2 * floor($potential_damage/2);
//		}
//		else {
//			// DCS reduces damage by 75% (We can't take off 0.5 of anything)
//			$potential_damage = floor(1.5 * floor($potential_damage/1.5));
//		}
//		if($potential_damage == 0) {
//			$result[4] = NORMAL_HIT;
//			return $result;
//		}
//	}
//	else {
//		$potential_damage = $weapons[$weapon][ARMOR_DAMAGE];
//	}
//
//	// No drones left, try to hit their armour
//	if($players[$defender][ARMOR] != 0 ) {
//		// Does the weapon do armor damage?
//		if($potential_damage) {
//			// Have we produced more damage than there are shields remaining?
//			if($potential_damage >= $players[$defender][ARMOR]) {
//				$result[2] = $players[$defender][ARMOR];
//				// Final hit
//				$result[4] = FINAL_HIT;
//			}
//			else {
//				$result[2] = NORMAL_HIT;
//				$result[2] = $potential_damage;
//			}
//		}
//		else {
//			$result[4] = SHIELD_ON_ARMOR;
//		}
//	}
//
//	return $result;
//}

?>

