<?
require_once(get_file_loc('SmrSector.class.inc'));
$sector =& SmrSector::getSector(SmrSession::$game_id, $player->getSectorID(), SmrSession::$account_id);
if ($player->getNewbieTurns() > 0)
	create_error('You are under newbie protection!');

//turns are taken b4 player fires.

require_once(get_file_loc('SmrForce.class.inc'));
$forces =& SmrForce::getForce($player->getGameID(), $player->getSectorID(), $owner_id);

if ($forces->getMines() == 0)
	create_error('No mines in sector! You should never be see this! Why are you looking at this! STOP! NOW! GO AWAY! PLEASE!!! STOP READING THIS NOW!!! PLEASE!!!');

// delete plotted course
$player->deletePlottedCourse();

// send message if scouts are present
if ($forces->hasSDs())
{

	$message = 'Your forces in sector '.$forces->getSectorID().' are being attacked by '.$player->getPlayerName();
	$forces->ping($message, $player);

}

$force_msg = array();


$container = array();
$container['url'] = 'skeleton.php';
$container['body'] = 'forces_attack.php';
$container['continue'] = 'yes';
$container['forced'] = 'yes';

// ********************************
// *
// * F o r c e s   a t t a c k
// *
// ********************************
$num_exits = 0;
if ($sector->getLinkUp() > 0)
	$num_exits += 1;
if ($sector->getLinkDown() > 0)
	$num_exits += 1;
if ($sector->getLinkLeft() > 0)
	$num_exits += 1;
if ($sector->getLinkRight() > 0)
	$num_exits += 1;



// fed ships take half damage from mines
if ($ship->getShipTypeID() == 20 || $ship->getShipTypeID() == 21 || $ship->getShipTypeID() == 22)
	$forces_damage = 10;
else
	$forces_damage = 20;

//formula......100% - ((your level) + (rand(1,7)*rand(1,7))) mines will hit for 20 damage each.
$percent_hitting = 100 - ($player->getLevelID() + (mt_rand(1,7) * mt_rand(1,7)));
//find out how many are going to attack you
$number_hitting = round($forces->getMines() * ($percent_hitting / 100));

if ($num_exits >= 3)
	$number_hitting = round($number_hitting / 2);

// fed ships take half damage from mines
$damage = $number_hitting * $forces_damage;

// If you hit mines with an active cloak you can blow your cloak
//if($number_hitting > 0 && $ship->cloak_overload()) {
//	$force_msg[] = '<span class='red bold''>WARNING:</span> Feedback through your shields has <span class='red'>DESTROYED</span> your cloaking device.<br />';
//}
//else {
	// Whatever happens the cloak gets disabled
	$ship->decloak();
//}



// Does attacker have shields?
if ($ship->getShields() > 0 && $number_hitting > 0) {

	// do we make more damage than shields left?
	if ($damage > $ship->getShields()) {

		// reduce damage to number of shields left
		$damage = $ship->getShields();

		// calc how many are actually hitting
		$number_hitting = ceil( $damage / $forces_damage );

	}

	// add the force_damage
	$force_damage += $damage;

	// subtract the shield damage
	$ship->decreaseShields($damage);

	// echo message
	$force_msg[] = '<span style="color:yellow;">'.$number_hitting.'</span> mines kamikaze themselves against <span style="color:yellow;">'.$player->getPlayerName().'</span>\'s ship for <span style="color:red;">'.$damage.'</span> shields.';

	//subtract mines that hit
	$forces->takeMines($number_hitting);

} elseif ($ship->getArmour() > 0 && $number_hitting > 0) {

	// do we make more damage than armor left?
	if ($damage > $ship->getArmour()) {

		// reduce damage to number of drones left
		$damage = $ship->getArmour();

		// calc how many are actually hitting
		$number_hitting = ceil( $damage / $forces_damage );

	}

	//subtract the damage
	$ship->decreaseArmour($damage);

	// add the force_damage
	$force_damage += $damage;

	// echo message
	$force_msg[] = '<span style="color:yellow;">'.$number_hitting.'</span> mines kamikaze themselves against <span style="color:yellow;">'.$player->getPlayerName().'</span>\'s ship destroying <span style="color:red;">'.$damage.'</span> armor.';

	//subtract mines that hit
	$forces->takeMines($number_hitting);

} elseif ($number_hitting == 0) {
	
	// echo message
	$force_msg[] = 'The mines fail to hit <span style="color:yellow;">'.$player->getPlayerName().'</span>.';
	
}

// ********************************
// *
// * A t t a c k e r   s h o o t s
// *
// ********************************
$attacker_total_msg = array();
$attacker_msg = array();
if ($player->getTurns() < 3) {

	// clear green exit	
	$player->setLastSectorID(0);
	$player->update();
	
	$attacker_msg[] = 'You do not have enough turns to return fire at the mines!';
	//return empty set so we dont gointo next part
	$db->query('SELECT * FROM player WHERE account_id = 0 AND player_id = 0 AND player_name = \'0\'');
	$cds = 'no';
	
} else {
	
	$db->query('SELECT * FROM ship_has_weapon, weapon_type ' .
		   'WHERE account_id = '.$player->getAccountID().' AND ' .
				 'game_id = '.SmrSession::$game_id.' AND ' .
				 'ship_has_weapon.weapon_type_id = weapon_type.weapon_type_id ' .
		   'ORDER BY order_id');
	// take the turns
	if ($db->nf()) {
		$taken = 'taken';
		$player->takeTurns(3,1);
	}
	$player->update();
	$cds = 'yes';
	
}

// iterate over all existing weapons
while ($db->next_record() && ($forces->getCDs() > 0 || $forces->getSDs() > 0 || $forces->getMines() > 0)) {

	$weapon_name = $db->f('weapon_name');
	$shield_damage = $db->f('shield_damage');
	$armor_damage = $db->f('armor_damage');
	$accuracy = $db->f('accuracy');

	if ($forces->getMines() > 0) {

		if ($armor_damage > 0) {

			// mines take 20 armor damage each
			$mines_dead = round($armor_damage / 20);

			// more damage than mines?
			if ($mines_dead > $forces->getMines())
				$mines_dead = $forces->getMines();

			// subtract mines that died
			$forces->takeMines($mines_dead);

			// add damage we did
			$attacker_damage += $mines_dead * 20;

			// echo message
			$attacker_msg[] = '<span style="color:yellow;">'.$player->getPlayerName().'</span> fires a '.$weapon_name.' at the forces destroying <span style="color:red;">'.$mines_dead.'</span> mines.';

		} elseif ($shield_damage > 0)
			$attacker_msg[] = '<span style="color:yellow;">'.$player->getPlayerName().'</span> fires a '.$weapon_name.' at the forces but it proves to be ineffective against the mines.';

	} elseif ($forces->getCDs() > 0) {

		if ($armor_damage > 0 && $forces->getCDs() > 0) {

			// combat drones take 3 armor damage each
			$drones_dead = floor( $armor_damage / 3 );

			// more damage than combat drones?
			if ($drones_dead > $forces->getCDs())
				$drones_dead = $forces->getCDs();

			// subtract scouts that died
			$forces->takeCDs($drones_dead);

			// add damage we did
			$attacker_damage += $drones_dead * 3;

			// echo message
			$attacker_msg[] = '<span style="color:yellow;">'.$player->getPlayerName().'</span> fires a '.$weapon_name.' at the forces and destroys <span style="color:red;">'.$drones_dead.'</span> combat drones.';

		} elseif ($armor_damage == 0 && $shield_damage > 0)
			$attacker_msg[] = '<span style="color:yellow;">$player->getPlayerName()</span> fires a '.$weapon_name.' at the forces but it proves to be ineffective against the armor of the drones';

	} elseif ($forces->getSDs() > 0) {

		if ($armor_damage > 0) {

			// scouts take 20 armor damage each
			$scouts_dead = round($armor_damage / 20);

			// more damage than scouts?
			if ($scouts_dead > $forces->getSDs())
				$scouts_dead = $forces->getSDs();

			// subtract scouts that died
			$forces->takeSDs($scouts_dead);

			// add damage we did
			$attacker_damage += $scouts_dead * 20;

			// echo message
			$attacker_msg[] = '<span style="color:yellow;">'.$player->getPlayerName().'</span> fires a '.$weapon_name.' at the forces and destroys <font color=red>'.$scouts_dead.'</font> scout drones.';

		} else
			$attacker_msg[] = '<span style="color:yellow;">'.$player->getPlayerName().'</span> fires a '.$weapon_name.' at the forces but it proves to be ineffective against the armor of the drones';

	}

}

// do we have drones?
if ($ship->getCDs() > 0 && ($forces->getMines() > 0 || $forces->getCDs() > 0 || $forces->getSDs() > 0) && $cds == 'yes') {

	if (empty($taken)) $player->takeTurns(3);
	$player->update();
	// Random(3 to 54) + Random(Attacker level/4 to Attacker level)
	$percent_attacking = (mt_rand(3, 53) + mt_rand($player->getLevelID() / 4, $player->getLevelID())) / 100;
	$number_attacking = round($percent_attacking * $ship->getCDs());

	// can not more attacking than we carry
	if ($number_attacking > $ship->getCDs())
		$number_attacking = $ship->getCDs();

	if ($forces->getMines() > 0) {

		// can we do more damage than mines left?
		if ($number_attacking > $forces->getMines())
			$number_attacking = $forces->getMines();

		// take mines
		$forces->takeMines($number_attacking);
		$ship->decreaseCDs($number_attacking);

		// accumulate attacker damage
		$attacker_damage += $number_attacking;

		// echo message
		$attacker_msg[] = '<span style="color:yellow;">'.$number_attacking.'</span> combat drones kamikaze themselves against the forces destroying <span style="color:red;">'.$number_attacking.'</span> mines.';

	// are there drones left?
	} elseif ($forces->getCDs() > 0) {

		// can we do more damage than drones left?
		if ($number_attacking * 2 > $forces->getCDs() * 3)
			$number_attacking = ceil($forces->getCDs() * 3 / 2);

		// cd's doing 2 damage
		$damage = $number_attacking * 2;

		// cd's take 3 damage each
		$forces->takeCDs(floor($damage / 3));

		// accumulate attacker damage
		$attacker_damage += $damage;

		// echo message
		$attacker_msg[] = '<span style="color:yellow;">'.$player->getPlayerName().'</span> launches <span style="color:yellow;">'.$number_attacking.'</span> drones at the forces destroying <span style="color:red;">' . floor ($damage / 3) . '</span> combat drones.';

	// are there scouts left?
	} elseif ($forces->getSDs() > 0) {

		// can we do more damage than scouts left?
		if ($number_attacking > $forces->getSDs() * 10)
			$number_attacking = $forces->getSDs() * 10;

		// cd's doing 2 damage
		$damage = $number_attacking * 2;

		// scouts take 20 damage each
		$forces->takeSDs(floor($damage / 20));

		// accumulate attacker damage
		$attacker_damage += $damage;

		// echo message
		$attacker_msg[] = '<span style="color:yellow;">'.$player->getPlayerName().'</span> launches <span style="color:yellow;">'.$number_attacking.'</span> drones at the forces destroying <span style="color:red;">' . floor ($damage / 20) . '</span> scout drones.';

	}

} // end of 'do we have drones'

// recalc forces expiration date
if($forces->getCDs() == 0 && $forces->getMines() == 0 && $forces->getSDs() == 1) {
	$days = 2;
}
else {
	$days = ceil(($forces->getCDs() + $forces->getSDs() + $forces->getMines()) / 10);
}
if ($days > 5) $days = 5;
$forces->setExpire(TIME + ($days * 86400));

// update forces
$forces->update();

// are forces dead?
if ($forces->getMines() == 0 && $forces->getCDs() == 0 && $forces->getSDs() == 0) {

	$attacker_msg[] = 'Forces are <span style="color:red;">DESTROYED!</span>';
	$container['continue'] = 'no';

}

// echo the overall damage
if ($attacker_damage > 0) {

	$attacker_msg[] = '<span style="color:yellow;">'.$player->getPlayerName().'</span> does a total of <span style="color:red;">'.$attacker_damage.'</span> damage.';
	// 5% of the damage goes to xp
	$player->increaseExperience($attacker_damage * .05);
	$player->update();

} else
	$attacker_msg[] = '<span style="color:yellow;">'.$player->getPlayerName().'</span> does absolutely no damage this round. Send the worthless lout back to the academy!';

$attacker_team_damage += $attacker_damage;
$attacker_total_msg[] = $attacker_msg;

$ship->update_hardware();

// is he dead now?
if ($ship->getShields() == 0 && $ship->getArmour() == 0)
{
	$player->killPlayerByForces($forces);
}

if ($player->isDead()) {

	$force_msg[] = '<span style="color:yellow;">'.$player->getPlayerName().'</span> is <span style="color:red;">DESTROYED!</span>';

	$player->setDead(false);
	$player->update();

	$container['continue'] = 'no';

}


// info for the next page
$container['force_msg'] = $force_msg;
$container['attacker_total_msg'] = $attacker_total_msg;
$container['owner_id'] = $owner_id;
forward($container);

?>
