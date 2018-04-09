<?php

require_once(get_file_loc('SmrSector.class.inc'));
$sector =& SmrSector::getSector(SmrSession::$game_id, $player->getSectorID(), SmrSession::$account_id);


		require_once(get_file_loc('smr_force.inc'));
if ($player->getNewbieTurns() > 0)
	create_error('You are under newbie protection!');

if ($player->getTurns() < 3)
	create_error('You do not have enough turns to attack these forces!');

$forces =& SmrForce::getForce($player->getGameID(), $player->getSectorID(), $var['owner_id']);

// take the turns
$player->takeTurns(3);
$player->update();

// delete plotted course
$player->deletePlottedCourse();

// send message if scouts are present
if ($forces->hasSDs()) {

	$message = 'Your forces in sector '.$forces->sector_id.' are being attacked by '.$player->getPlayerName();
	$player->sendMessage($forces->owner_id, $SCOUTMSG, $message);
	//insert into ticker
	$owner_id = $var['owner_id'];
	$time = time();
	$db->query('SELECT * FROM player_has_ticker WHERE account_id = '.$owner_id.' AND game_id = '.$player->getGameID().' AND type = \'scout\'');
	if ($db->next_record()) {
				
		$db->query('SELECT * FROM player_has_ticker WHERE account_id = '.$player->getAccountID().' AND type = \'block\'');
		if (!$db->next_record()) $db->query('UPDATE player_has_ticker SET recent = ' . $db->escape_string($message, false) . ', time = '.$time.' WHERE account_id = '.$owner_id.' AND game_id = '.$player->getGameID());
		
	}

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

$force_msg = array();

if ($player->getAllianceID() != 0) {

	$db->query('SELECT * FROM player ' .
			   'WHERE game_id = '.$player->getGameID().' AND ' .
					 'alliance_id = '.$player->getAllianceID().' AND ' .
					 'sector_id = '.$player->getSectorID().' AND ' .
					 'land_on_planet = \'FALSE\' AND ' .
					 'newbie_turns = 0 ' .
			   'ORDER BY rand() LIMIT 1');

} else {

	$db->query('SELECT * FROM player ' .
			   'WHERE game_id = '.$player->getGameID().' AND ' .
					 'sector_id = '.$player->getSectorID().' AND ' .
					 'account_id = '.$player->getAccountID().' AND ' .
					 'land_on_planet = \'FALSE\' AND ' .
					 'newbie_turns = 0 ' .
			   'ORDER BY rand() LIMIT 1');

}
if ($db->next_record()) {

	$curr_attacker =& SmrPlayer::getPlayer($db->f('account_id'), SmrSession::$game_id);
	$curr_attacker_ship = new SMR_SHIP($db->f('account_id'), SmrSession::$game_id);
	
	// disable cloak
	$curr_attacker_ship->disable_cloak();
	
	// fed ships take half damage from mines
	if ($curr_attacker_ship->ship_type_id == 20 || $curr_attacker_ship->ship_type_id == 21 || $curr_attacker_ship->ship_type_id == 22)
		$forces_damage = 10;
	else
		$forces_damage = 20;

	// Mines attacking
	if ($forces->hasMines()) {

		//formula......100% - ((your level) + (rand(1,7)*rand(1,7))) mines will hit for 20 damage each.
		$percent_hitting = 100 - (($curr_attacker->level_id) + (mt_rand(1,7) * mt_rand(1,7)));
		//find out how many are going to attack you
		$number_hitting = round($forces->getMines() * ($percent_hitting / 100));

		// fed ships take half damage from mines
		$damage = $number_hitting * $forces_damage;

		//Does attacker have shields?
		if ($curr_attacker_ship->hardware[HARDWARE_SHIELDS] > 0 && $number_hitting > 0) {

			// do we make more damage than shields left?
			if ($damage > $curr_attacker_ship->hardware[HARDWARE_SHIELDS]) {

				// reduce damage to number of shields left
				$damage = $curr_attacker_ship->hardware[HARDWARE_SHIELDS];

				// calc how many are actually hitting
				$number_hitting = ceil( $damage / $forces_damage );

			}

			// add the force_damage
			$force_damage += $damage;

			// subtract the shield damage
			$curr_attacker_ship->hardware[HARDWARE_SHIELDS] -= $damage;

			// echo message
			$force_msg[] = '<span style="color:yellow;">'.$number_hitting.'</span> mines kamikaze themselves against <span style="color:yellow;">'.$curr_attacker->getPlayerName().'</span>\'s ship for <span style="color:red;">'.$damage.'</span> shields.';

			//subtract mines that hit
			$forces->takeMines($number_hitting);

		} elseif ($curr_attacker_ship->hardware[HARDWARE_ARMOUR] > 0 && $number_hitting > 0) {

			// do we make more damage than armour left?
			if ($damage > $curr_attacker_ship->hardware[HARDWARE_ARMOUR]) {

				// reduce damage to number of drones left
				$damage = $curr_attacker_ship->hardware[HARDWARE_ARMOUR];

				// calc how many are actually hitting
				$number_hitting = ceil( $damage / $forces_damage );

			}

			//subtract the damage
			$curr_attacker_ship->hardware[HARDWARE_ARMOUR] -= $damage;

			// add the force_damage
			$force_damage += $damage;

			// echo message
			$force_msg[] = '<span style="color:yellow;">'.$number_hitting.'</span> mines kamikaze themselves against <span style="color:yellow;">'.$curr_attacker->getPlayerName().'</span>\'s ship destroying <span style="color:red;">'.$damage.'</span> armour.';

			//subtract mines that hit
			$forces->takeMines($number_hitting);

		}

	}

	// is he dead now?
	if ($curr_attacker_ship->hardware[HARDWARE_SHIELDS] == 0 && $curr_attacker_ship->hardware[HARDWARE_ARMOUR] == 0)
		$curr_attacker->mark_dead();

	if ($forces->hasSDs() && !$curr_attacker->isDead()) {

		$number_hitting = $forces->getSDs();

		// fed ships take half damage from drones
		$damage = $number_hitting * $forces_damage;

		// does the attacker have shields left?
		if ($curr_attacker_ship->hardware[HARDWARE_SHIELDS] > 0) {

			//Can we destroy all the shields or do they not have enough?
			if ($damage > $curr_attacker_ship->hardware[HARDWARE_SHIELDS]) {

				// reduce damage to number of shields left
				$damage = $curr_attacker_ship->hardware[HARDWARE_SHIELDS];

				// calc how many are actually hitting
				$number_hitting = ceil( $damage / $forces_damage );

			}
			
			//scouts kamikaze
			$forces->takeSDs($number_hitting);
			
			// accumulate the force_damage
			$force_damage += $damage;

			// subtract the shield damage
			$curr_attacker_ship->hardware[HARDWARE_SHIELDS] -= $damage;

			// echo message
			$force_msg[] = '<span style="color:yellow;">'.$number_hitting.'</span> scout drones kamikaze themselves against <span style="color:yellow;">'.$curr_attacker->getPlayerName().'</span>\'s ship destroying <span style="color:red;">'.$damage.'</span> shields.';

		// does the attacker has drones left?
		} elseif ($curr_attacker_ship->hardware[HARDWARE_COMBAT] > 0) {

			// do we make more damage than drones left?
			if ($damage > $curr_attacker_ship->hardware[HARDWARE_COMBAT] * 3) {

				// reduce damage to number of drones left
				$damage = $curr_attacker_ship->hardware[HARDWARE_COMBAT] * 3;

				// calc how many are actually hitting
				$number_hitting = ceil( $damage / $forces_damage );

			}
			
			//scouts kamikaze
			$forces->takeSDs($number_hitting);

			// add the force_damage
			$force_damage += $damage;

			// subtract the shield damage
			$curr_attacker_ship->hardware[HARDWARE_COMBAT] -= round( $damage / 3 );

			// echo message
			$force_msg[] = '<span style="color:yellow;">'.$number_hitting.'</span> scout drones kamikaze themselves against <span style="color:yellow;">'.$curr_attacker->getPlayerName().'</span>\'s ship destroying <span style="color:red;">' . round( $damage / 3 ) . '</span> drones.';

		// does the attacker has armour left?
		} elseif ($curr_attacker_ship->hardware[HARDWARE_ARMOUR] > 0) {

			//can we kill all armour?
			if ($damage > $curr_attacker_ship->hardware[HARDWARE_ARMOUR]) {

				// reduce damage to number of armour left
				$damage = $curr_attacker_ship->hardware[HARDWARE_ARMOUR];

				// calc how many are actually hitting
				$number_hitting = ceil( $damage / $forces_damage );

			}
			
			//scouts kamikaze
			$forces->takeSDs($number_hitting);

			// add the force_damage
			$force_damage += $damage;

			//subtract the damage
			$curr_attacker_ship->hardware[HARDWARE_ARMOUR] -= $damage;

			// echo message
			$force_msg[] = '<span style="color:yellow;">'.$number_hitting.'</span> scout drones kamikaze themselves against <span style="color:yellow;">'.$curr_attacker->getPlayerName().'</span>\'s ship destroying <span style="color:red;">'.$damage.'</span> armour.';

		}

	} //end of scout drones

	// is he dead now?
	if ($curr_attacker_ship->hardware[HARDWARE_SHIELDS] == 0 && $curr_attacker_ship->hardware[HARDWARE_ARMOUR] == 0)
		$curr_attacker->mark_dead();

	if ($forces->hasCDs() && !$curr_attacker->isDead()) {

		//find out how many are going to attack you
		$number_hitting = round($forces->getCDs() * mt_rand(3, 54) / 100);

		// for drones we adept the force damage.
		// mines and sd's doing 20 damage to normal ships
		// drones only 2 damage
		$forces_damage /= 10;

		// fed ships take half damage from drones
		$damage = $number_hitting * $forces_damage;
		//if we have dcs drones do less
		if ($curr_attacker_ship->hardware[HARDWARE_DCS] == 1)
			$damage = round( $damage / (4 / 3) );

		// does the attacker has shields left?
		if ($curr_attacker_ship->hardware[HARDWARE_SHIELDS] > 0) {

			//Can we destroy all the shields or do they not have enough?
			if ($damage > $curr_attacker_ship->hardware[HARDWARE_SHIELDS]) {

				// reduce damage to number of shields left
				$damage = $curr_attacker_ship->hardware[HARDWARE_SHIELDS];

				// calc how many are actually hitting
				$number_hitting = ceil( $damage / $forces_damage );

			}

			// accumulate the force_damage
			$force_damage += $damage;

			// subtract the shield damage
			$curr_attacker_ship->hardware[HARDWARE_SHIELDS] -= $damage;

			// echo message
			$force_msg[] = '<span style="color:yellow;">'.$number_hitting.'</span> combat drones drones launch at <span style="color:yellow;">'.$curr_attacker->getPlayerName().'</span> destroying <span style="color:red;">'.$damage.'</span> shields.';

		// does the attacker has drones left?
		} elseif ($curr_attacker_ship->hardware[HARDWARE_COMBAT] > 0) {

			// do we make more damage than drones left?
			if ($damage > $curr_attacker_ship->hardware[HARDWARE_COMBAT] * 3) {

				// reduce damage to number of drones left
				$damage = $curr_attacker_ship->hardware[HARDWARE_COMBAT] * 3;

				// calc how many are actually hitting
				$number_hitting = ceil( $damage / $forces_damage );

			}

			// add the force_damage
			$force_damage += $damage;

			// subtract the shield damage
			$curr_attacker_ship->hardware[HARDWARE_COMBAT] -= round( $damage / 3 );

			// echo message
			$force_msg[] = '<span style="color:yellow;">'.$number_hitting.'</span> combat drones drones launch at <span style="color:yellow;">'.$curr_attacker->getPlayerName().'</span> destroying <span style="color:red;">'.$damage.'</span> drones.';

		// does the attacker has armour left?
		} elseif ($curr_attacker_ship->hardware[HARDWARE_ARMOUR] > 0) {

			//can we kill all armour?
			if ($damage > $curr_attacker_ship->hardware[HARDWARE_ARMOUR]) {

				// reduce damage to number of armour left
				$damage = $curr_attacker_ship->hardware[HARDWARE_ARMOUR];

				// calc how many are actually hitting
				$number_hitting = ceil( $damage / $forces_damage );

			}

			// add the force_damage
			$force_damage += $damage;

			//subtract the damage
			$curr_attacker_ship->hardware[HARDWARE_ARMOUR] -= $damage;

			// echo message
			$force_msg[] = '<span style="color:yellow;">'.$number_hitting.'</span> combat drones drones launch at <span style="color:yellow;">'.$curr_attacker->getPlayerName().'</span> destroying <span style="color:red;">'.$damage.'</span> armour.';

		}

	}

	// update ship
	$curr_attacker_ship->update_hardware();

	// is he dead now?
	if ($curr_attacker_ship->hardware[HARDWARE_SHIELDS] == 0 && $curr_attacker_ship->hardware[HARDWARE_ARMOUR] == 0)
		$curr_attacker->mark_dead();

	// is he dead?
	if ($curr_attacker->isDead()) {

		// echo message
		$force_msg[] = '<span style="color:yellow;">'.$curr_attacker->getPlayerName().'</span> is <span style="color:red;">DESTROYED!</span>';

		// run through dead methods for player and ship
		$curr_attacker->died_by_forces($forces->getOwnerID());
		$curr_attacker_ship->getPod();

		// if we are the guy who's dead
		if ($curr_attacker->getAccountID() == $player->getAccountID()) {

			// we don't want to get a pod screen
			$curr_attacker->setDead(false);

			// and there shouldn't be a cont' button
			$container['continue'] = 'no';

		}

		// make it permanent
		$curr_attacker->update();

	}
	if (!$forces->exists()) {
		$attacker_msg[] = 'Forces are <span style="color:red;">DESTROYED!</span>';
		$container['continue'] = 'no';
	}

}

// echo the overall damage
if ($force_damage > 0)
	$force_msg[] = '<br>This team does a total of <span style="color:red;">$force_damage</span> damage in this round of combat.';
else
	$force_msg[] = '<br>This team does no damage at all. You call that a team? They need a better recruiter.';

// ********************************
// *
// * A t t a c k e r   s h o o t s
// *
// ********************************
$attacker_total_msg = array();
$attacker_msg = array();

if ($player->getAllianceID() != 0) {

	$db->query('SELECT * FROM player ' .
			   'WHERE game_id = '.$player->getGameID().' AND ' .
					 'alliance_id = '.$player->getAllianceID().' AND ' .
					 'sector_id = '.$player->getSectorID().' AND ' .
					 'land_on_planet = \'FALSE\' AND ' .
					 'newbie_turns = 0 ' .
			   'ORDER BY rand() LIMIT 10');

} else {

	$db->query('SELECT * FROM player ' .
			   'WHERE game_id = '.$player->getGameID().' AND ' .
					 'sector_id = '.$player->getSectorID().' AND ' .
					 'account_id = '.$player->getAccountID().' AND ' .
					 'land_on_planet = \'FALSE\' AND ' .
					 'newbie_turns = 0');

}

$db2 = new SmrMySqlDatabase();

while ($db->next_record() && ($forces->hasCDs() || $forces->hasSDs() || $forces->hasMines())) {

	$curr_attacker =& SmrPlayer::getPlayer($db->f('account_id'), SmrSession::$game_id);
	$curr_attacker_ship = new SMR_SHIP($db->f('account_id'), SmrSession::$game_id);

	// disable cloak
	$curr_attacker_ship->disable_cloak();

	$db2->query('SELECT * FROM ship_has_weapon, weapon_type ' .
				'WHERE account_id = '.$curr_attacker->getAccountID().' AND ' .
					  'game_id = '.SmrSession::$game_id.' AND ' .
					  'ship_has_weapon.weapon_type_id = weapon_type.weapon_type_id ' .
				'ORDER BY order_id');

	// iterate over all existing weapons
	while ($db2->next_record() && ($forces->hasCDs() || $forces->hasSDs() || $forces->hasMines())) {

		$weapon_name = $db2->f('weapon_name');
		$shield_damage = $db2->f('shield_damage');
		$armour_damage = $db2->f('armour_damage');
		$accuracy = $db2->f('accuracy');

		if ($forces->hasMines()) {

			if ($armour_damage > 0) {

				// mines take 20 armour damage each
				$mines_dead = round($armour_damage / 20);

				// more damage than mines?
				if ($mines_dead > $forces->getMines())
					$mines_dead = $forces->getMines();

				// subtract mines that died
				$forces->takeMines($mines_dead);

				// add damage we did
				$attacker_damage += $mines_dead * 20;

				// echo message
				$attacker_msg[] = '<span style="color:yellow;">'.$curr_attacker->getPlayerName().'</span> fires a '.$weapon_name.' at the forces destroying <span style="color:red;">'.$mines_dead.'</span> mines.';

			} elseif ($shield_damage > 0)
				$attacker_msg[] = '<span style="color:yellow;">'.$curr_attacker->getPlayerName().'</span> fires a '.$weapon_name.' at the forces but it proves to be ineffective against the mines.';

		} elseif ($forces->hasCDs()) {

			if ($armour_damage > 0 && $forces->hasCDs()) {

				// combat drones take 3 armour damage each
				$drones_dead = floor( $armour_damage / 3 );

				// more damage than combat drones?
				if ($drones_dead > $forces->getCDs())
					$drones_dead = $forces->getCDs();

				// subtract scouts that died
				$forces->takeCDs($drones_dead);

				// add damage we did
				$attacker_damage += $drones_dead * 3;

				// echo message
				$attacker_msg[] = '<span style="color:yellow;">'.$curr_attacker->getPlayerName().'</span> fires a '.$weapon_name.' at the forces and destroys <span style="color:red;">'.$drones_dead.'</span> combat drones.';

			} elseif ($armour_damage == 0 && $shield_damage > 0)
				$attacker_msg[] = '<span style="color:yellow;">'.$curr_attacker->getPlayerName().'</span> fires a '.$weapon_name.' at the forces but it proves to be ineffective against the armour of the drones';

		} elseif ($forces->hasSDs()) {

			if ($armour_damage > 0) {

				// scouts take 20 armour damage each
				$scouts_dead = round($armour_damage / 20);

				// more damage than scouts?
				if ($scouts_dead > $forces->getSDs())
					$scouts_dead = $forces->getSDs();

				// subtract scouts that died
				$forces->takeSDs($scouts_dead);

				// add damage we did
				$attacker_damage += $scouts_dead * 20;

				// echo message
				$attacker_msg[] = '<span style="color:yellow;">'.$curr_attacker->getPlayerName().'</span> fires a '.$weapon_name.' at the forces and destroys <font color=red>'.$scouts_dead.'</font> scout drones.';

			} else
				$attacker_msg[] = '<span style="color:yellow;">'.$curr_attacker->getPlayerName().'</span> fires a '.$weapon_name.' at the forces but it proves to be ineffective against the armour of the drones';

		}

	}

	// do we have drones?
	if ($curr_attacker_ship->hardware[HARDWARE_COMBAT] > 0 && ($forces->hasMines() || $forces->hasCDs() || $forces->hasSDs())) {

		// Random(3 to 54) + Random(Attacker level/4 to Attacker level)
		$percent_attacking = (mt_rand(3, 53) + mt_rand($curr_attacker->level_id / 4, $curr_attacker->level_id)) / 100;
		$number_attacking = round($percent_attacking * $curr_attacker_ship->hardware[HARDWARE_COMBAT]);

		// can not more attacking than we carry
		if ($number_attacking > $curr_attacker_ship->hardware[HARDWARE_COMBAT])
			$number_attacking = $curr_attacker_ship->hardware[HARDWARE_COMBAT];

		if ($forces->hasMines()) {

			// can we do more damage than mines left?
			if ($number_attacking > $forces->getMines())
				$number_attacking = $forces->getMines();

			// take mines
			$forces->takeMines($number_attacking);
			$curr_attacker_ship->hardware[HARDWARE_COMBAT] -= $number_attacking;

			// accumulate attacker damage
			$attacker_damage += $number_attacking;

			// echo message
			$attacker_msg[] = '<span style="color:yellow;">'.$number_attacking.'</span> combat drones kamikaze themselves against the forces destroying <span style="color:red;">'.$number_attacking.'</span> mines.';

		// are there drones left?
		} elseif ($forces->hasCDs()) {

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
			$attacker_msg[] = '<span style="color:yellow;">'.$curr_attacker->getPlayerName().'</span> launches <span style="color:yellow;">'.$number_attacking.'</span> drones at the forces destroying <span style="color:red;">' . floor ($damage / 20) . '</span> combat drones.';

		// are there scouts left?
		} elseif ($forces->hasSDs()) {

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
			$attacker_msg[] = '<span style="color:yellow;">'.$curr_attacker->getPlayerName().'</span> launches <span style="color:yellow;">'.$number_attacking.'</span> drones at the forces destroying <span style="color:red;">' . floor ($damage / 20) . '</span> scout drones.';

		}

	} // end of 'do we have drones'

	// are forces dead?
	if (!$forces->exists()) {

		$attacker_msg[] = 'Forces are <span style="color:red;">DESTROYED!</span>';
		$container['continue'] = 'no';

	}

	// echo the overall damage
	if ($attacker_damage > 0) {

		$attacker_msg[] = '<span style="color:yellow;">'.$curr_attacker->getPlayerName().'</span> does a total of <span style="color:red;">'.$attacker_damage.'</span> damage.';

		// 25% of the damage goes to xp
		$curr_attacker->increaseExperience($attacker_damage * .05);

	} else
		$attacker_msg[] = '<span style="color:yellow;">'.$curr_attacker->getPlayerName().'</span> does absolutely no damage this round. Send the worthless lout back to the academy!';

	$attacker_team_damage += $attacker_damage;
	$attacker_total_msg[] = $attacker_msg;

	//reset damage for each person and the array
	$attacker_damage = 0;
	$attacker_msg = array();

	$curr_attacker->update();
	$curr_attacker_ship->update_hardware();

}

// recalc forces expiration date
$forces->updateExpire();
// update forces
$forces->update();

// echo the overall damage
if ($attacker_team_damage > 0)
	$attacker_msg[] = '<br>This team does a total of <span style="color:red;">$attacker_team_damage</span> damage in this round of combat.';
else
	$attacker_msg[] = '<br>This team does no damage at all. You call that a team? They need a better recruiter.';

$attacker_total_msg[] = $attacker_msg;

// info for the next page
$container['force_msg'] = $force_msg;
$container['attacker_total_msg'] = $attacker_total_msg;
transfer('owner_id');

SmrPlayer::refreshCache();
SmrShip::refreshCache();
SmrForce::refreshCache();
forward($container);
