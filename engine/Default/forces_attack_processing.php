<?

if ($player->getNewbieTurns() > 0)
	create_error('You are under newbie protection!');

if ($player->getTurns() < 3)
	create_error('You do not have enough turns to attack these forces!');

require_once(get_file_loc('SmrForce.class.inc'));
$forces =& SmrForce::getForce($player->getGameID(), $player->getSectorID(), $var['owner_id']);

if(!$forces->exists())
	create_error('These forces no longer exist.');

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


require_once(get_file_loc('SmrSector.class.inc'));
$sector =& SmrSector::getSector(SmrSession::$game_id, $player->getSectorID(), SmrSession::$account_id);
$attackers =& $sector->getFightingTradersAgainstForces($player, $forces);

//decloak all attackers
foreach($attackers as &$attacker)
{
	$attacker->getShip()->decloak();
} unset($attacker);

$results['Forces'] =& $forces->shootPlayers($attackers,false);

foreach($attackers as &$attacker)
{
	$playerResults =& $attacker->shootForces($forces);
	$results['Attackers']['Traders'][$teamPlayer->getAccountID()]  =& $playerResults;
	$results['Attackers']['TotalDamage'] += $playerResults['TotalDamage'];
} unset($attacker);

$ship->removeUnderAttack(); //Don't show attacker the under attack message.

$serializedResults = serialize($results);
$db->query('INSERT INTO combat_logs VALUES(\'\',' . $player->getGameID() . ',\'FORCE\',' . $player->getSectorID() . ',' . TIME . ',' . $player->getAccountID() . ',' . $player->getAllianceID() . ',' . $var['target'] . ',' . $targetPlayer->getAllianceID() . ',' . $db->escape_string(gzcompress($serializedResults)) . ', \'FALSE\')');
unserialize($serializedResults); //because of references we have to undo this.

$container = array();
$container['url'] = 'skeleton.php';
$container['body'] = 'forces_attack.php';

// If their target is dead there is no continue attack button
if($forces->exist())
	$container['owner_id'] = $var['owner_id'];
else
	$container['owner_id'] = 0;

// If they died on the shot they get to see the results
if($player->isDead())
{
	$container['override_death'] = TRUE;
	$container['target'] = 0;
}

$container['forced'] = false;
$container['results'] = $serializedResults;
forward($container);



//// ********************************
//// *
//// * A t t a c k e r   s h o o t s
//// *
//// ********************************
//$attacker_total_msg = array();
//$attacker_msg = array();
//
//if ($player->getAllianceID() != 0) {
//
//	$db->query('SELECT * FROM player ' .
//			   'WHERE game_id = '.$player->getGameID().' AND ' .
//					 'alliance_id = '.$player->getAllianceID().' AND ' .
//					 'sector_id = '.$player->getSectorID().' AND ' .
//					 'land_on_planet = \'FALSE\' AND ' .
//					 'newbie_turns = 0 ' .
//			   'ORDER BY rand() LIMIT 10');
//
//} else {
//
//	$db->query('SELECT * FROM player ' .
//			   'WHERE game_id = '.$player->getGameID().' AND ' .
//					 'sector_id = '.$player->getSectorID().' AND ' .
//					 'account_id = '.$player->getAccountID().' AND ' .
//					 'land_on_planet = \'FALSE\' AND ' .
//					 'newbie_turns = 0');
//
//}
//
//$db2 = new SMR_DB();
//
//while ($db->next_record() && ($forces->getCDs() > 0 || $forces->getSDs() > 0 || $forces->getMines() > 0)) {
//
//	$curr_attacker =& SmrPlayer::getPlayer($db->f('account_id'), SmrSession::$game_id);
//	$curr_attacker_ship =& $curr_attacker->getShip();
//
//	// disable cloak
//	$curr_attacker_ship->decloak();
//
//	$db2->query('SELECT * FROM ship_has_weapon, weapon_type ' .
//				'WHERE account_id = '.$curr_attacker->getAccountID().' AND ' .
//					  'game_id = '.SmrSession::$game_id.' AND ' .
//					  'ship_has_weapon.weapon_type_id = weapon_type.weapon_type_id ' .
//				'ORDER BY order_id');
//
//	// iterate over all existing weapons
//	while ($db2->next_record() && ($forces->getCDs() > 0 || $forces->getSDs() > 0 || $forces->getMines() > 0)) {
//
//		$weapon_name = $db2->f('weapon_name');
//		$shield_damage = $db2->f('shield_damage');
//		$armor_damage = $db2->f('armor_damage');
//		$accuracy = $db2->f('accuracy');
//
//		if ($forces->getMines() > 0) {
//
//			if ($armor_damage > 0) {
//
//				// mines take 20 armor damage each
//				$mines_dead = round($armor_damage / 20);
//
//				// more damage than mines?
//				if ($mines_dead > $forces->getMines())
//					$mines_dead = $forces->getMines();
//
//				// subtract mines that died
//				$forces->takeMines($mines_dead);
//
//				// add damage we did
//				$attacker_damage += $mines_dead * 20;
//
//				// echo message
//				$attacker_msg[] = '<span style="color:yellow;">'.$curr_attacker->getPlayerName().'</span> fires a '.$weapon_name.' at the forces destroying <span style="color:red;">'.$mines_dead.'</span> mines.';
//
//			} elseif ($shield_damage > 0)
//				$attacker_msg[] = '<span style="color:yellow;">'.$curr_attacker->getPlayerName().'</span> fires a '.$weapon_name.' at the forces but it proves to be ineffective against the mines.';
//
//		} elseif ($forces->getCDs() > 0) {
//
//			if ($armor_damage > 0 && $forces->getCDs() > 0) {
//
//				// combat drones take 3 armor damage each
//				$drones_dead = floor( $armor_damage / 3 );
//
//				// more damage than combat drones?
//				if ($drones_dead > $forces->getCDs())
//					$drones_dead = $forces->getCDs();
//
//				// subtract scouts that died
//				$forces->takeCDs($drones_dead);
//
//				// add damage we did
//				$attacker_damage += $drones_dead * 3;
//
//				// echo message
//				$attacker_msg[] = '<span style="color:yellow;">'.$curr_attacker->getPlayerName().'</span> fires a '.$weapon_name.' at the forces and destroys <span style="color:red;">'.$drones_dead.'</span> combat drones.';
//
//			} elseif ($armor_damage == 0 && $shield_damage > 0)
//				$attacker_msg[] = '<span style="color:yellow;">'.$curr_attacker->getPlayerName().'</span> fires a '.$weapon_name.' at the forces but it proves to be ineffective against the armor of the drones';
//
//		} elseif ($forces->getSDs() > 0) {
//
//			if ($armor_damage > 0) {
//
//				// scouts take 20 armor damage each
//				$scouts_dead = round($armor_damage / 20);
//
//				// more damage than scouts?
//				if ($scouts_dead > $forces->getSDs())
//					$scouts_dead = $forces->getSDs();
//
//				// subtract scouts that died
//				$forces->takeSDs($scouts_dead);
//
//				// add damage we did
//				$attacker_damage += $scouts_dead * 20;
//
//				// echo message
//				$attacker_msg[] = '<span style="color:yellow;">'.$curr_attacker->getPlayerName().'</span> fires a '.$weapon_name.' at the forces and destroys <font color=red>'.$scouts_dead.'</font> scout drones.';
//
//			} else
//				$attacker_msg[] = '<span style="color:yellow;">'.$curr_attacker->getPlayerName().'</span> fires a '.$weapon_name.' at the forces but it proves to be ineffective against the armor of the drones';
//
//		}
//
//	}
//
//	// do we have drones?
//	if ($curr_attacker_ship->getCDs() > 0 && ($forces->getMines() > 0 || $forces->getCDs() > 0 || $forces->getSDs() > 0)) {
//
//		// Random(3 to 54) + Random(Attacker level/4 to Attacker level)
//		$percent_attacking = (mt_rand(3, 53) + mt_rand($curr_attacker->level_id / 4, $curr_attacker->level_id)) / 100;
//		$number_attacking = round($percent_attacking * $curr_attacker_ship->getCDs());
//
//		// can not more attacking than we carry
//		if ($number_attacking > $curr_attacker_ship->getCDs())
//			$number_attacking = $curr_attacker_ship->getCDs();
//
//		if ($forces->getMines() > 0) {
//
//			// can we do more damage than mines left?
//			if ($number_attacking > $forces->getMines())
//				$number_attacking = $forces->getMines();
//
//			// take mines
//			$forces->takeMines($number_attacking);
//			$curr_attacker_ship->decreaseCDs($number_attacking);
//
//			// accumulate attacker damage
//			$attacker_damage += $number_attacking;
//
//			// echo message
//			$attacker_msg[] = '<span style="color:yellow;">'.$number_attacking.'</span> combat drones kamikaze themselves against the forces destroying <span style="color:red;">'.$number_attacking.'</span> mines.';
//
//		// are there drones left?
//		} elseif ($forces->getCDs() > 0) {
//
//			// can we do more damage than drones left?
//			if ($number_attacking * 2 > $forces->getCDs() * 3)
//				$number_attacking = ceil($forces->getCDs() * 3 / 2);
//
//			// cd's doing 2 damage
//			$damage = $number_attacking * 2;
//
//			// cd's take 3 damage each
//			$forces->takeCDs(floor($damage / 3));
//
//			// accumulate attacker damage
//			$attacker_damage += $damage;
//
//			// echo message
//			$attacker_msg[] = '<span style="color:yellow;">'.$curr_attacker->getPlayerName().'</span> launches <span style="color:yellow;">'.$number_attacking.'</span> drones at the forces destroying <span style="color:red;">' . floor ($damage / 20) . '</span> combat drones.';
//
//		// are there scouts left?
//		} elseif ($forces->getSDs() > 0) {
//
//			// can we do more damage than scouts left?
//			if ($number_attacking > $forces->getSDs() * 10)
//				$number_attacking = $forces->getSDs() * 10;
//
//			// cd's doing 2 damage
//			$damage = $number_attacking * 2;
//
//			// scouts take 20 damage each
//			$forces->takeSDs(floor($damage / 20));
//
//			// accumulate attacker damage
//			$attacker_damage += $damage;
//
//			// echo message
//			$attacker_msg[] = '<span style="color:yellow;">'.$curr_attacker->getPlayerName().'</span> launches <span style="color:yellow;">'.$number_attacking.'</span> drones at the forces destroying <span style="color:red;">' . floor ($damage / 20) . '</span> scout drones.';
//
//		}
//
//	} // end of 'do we have drones'
//
//	// are forces dead?
//	if ($forces->getMines() < 1 && $forces->getCDs() < 1 && $forces->getSDs() < 1) {
//
//		$attacker_msg[] = 'Forces are <span style="color:red;">DESTROYED!</span>';
//		$container['continue'] = 'no';
//
//	}
//
//	// echo the overall damage
//	if ($attacker_damage > 0) {
//
//		$attacker_msg[] = '<span style="color:yellow;">'.$curr_attacker->getPlayerName().'</span> does a total of <span style="color:red;">'.$attacker_damage.'</span> damage.';
//
//		// 25% of the damage goes to xp
//		$curr_attacker->increaseExperience($attacker_damage * .05);
//
//	} else
//		$attacker_msg[] = '<span style="color:yellow;">'.$curr_attacker->getPlayerName().'</span> does absolutely no damage this round. Send the worthless lout back to the academy!';
//
//	$attacker_team_damage += $attacker_damage;
//	$attacker_total_msg[] = $attacker_msg;
//
//	//reset damage for each person and the array
//	$attacker_damage = 0;
//	$attacker_msg = array();
//
//	$curr_attacker->update();
//	$curr_attacker_ship->update_hardware();
//
//}
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
//// update forces
//$forces->update();
//
//// echo the overall damage
//if ($attacker_team_damage > 0)
//	$attacker_msg[] = '<br>This team does a total of <span style="color:red;">$attacker_team_damage</span> damage in this round of combat.';
//else
//	$attacker_msg[] = '<br>This team does no damage at all. You call that a team? They need a better recruiter.';
//
//$attacker_total_msg[] = $attacker_msg;
////
////// info for the next page
////$container['force_msg'] = $force_msg;
////$container['attacker_total_msg'] = $attacker_total_msg;
////transfer('owner_id');
////forward($container);

?>