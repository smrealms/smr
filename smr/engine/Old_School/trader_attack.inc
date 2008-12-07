<?
require_once(get_file_loc('SmrSector.class.inc'));
$sector =& SmrSector::getSector(SmrSession::$game_id, $player->getSectorID(), SmrSession::$account_id);

function shoot_trader($attacker_team, $defender_team) {

	global $session, $sector_id;

	$db = new SMR_DB();

	$damage_msgs = array();
	for ($i = 0; $i < $attacker_team->get_fleet_size(); $i++) {

		// get attacker
		$curr_att_id = $attacker_team->next(true);
		$curr_attacker =& SmrPlayer::getPlayer($curr_att_id, SmrSession::$game_id);
		$curr_attacker_ship =& SmrShip::getShip(SmrSession::$game_id,$curr_attacker->getAccountID());
		//used in kill table so we have a real sector id not an HQ
		$this_sector = $sector_id;
		// disable cloak
		$curr_attacker_ship->disable_cloak();

		// the damage this attacker is going to do
		$attacker_damage = 0;
		$damage_msg = array();

		$weapon = new SMR_DB();
		$weapon->query('SELECT * FROM ship_has_weapon NATURAL JOIN weapon_type ' .
					   'WHERE account_id = '.$curr_attacker->getAccountID().' AND ' .
							 'game_id = '.$curr_attacker->getGameID().' ' .
					   'ORDER BY order_id');

		// iterate over all existing weapons
		while ($weapon->next_record()) {

			// get defender
			$curr_defender =& SmrPlayer::getPlayer($defender_team->next(false), $curr_attacker->getGameID());

			//vars
			$weapon_name = $weapon->f('weapon_name');
			$shield_damage = $weapon->f('shield_damage');
			$armor_damage = $weapon->f('armor_damage');
			$accuracy = $weapon->f('accuracy');

			// maybe defender is already dead?
			//some times it won't run it correctly so this is a fix for that
			if ($curr_defender->dead == 'TRUE') {

				$damage_msg[] = '<span style="color:yellow;">'.$curr_attacker->getPlayerName().'</span> fires a '.$weapon_name.' at debris that once was <span style="color:yellow;">'.$curr_defender->getPlayerName().'</span>.';
				continue;

			}
			if ($curr_defender->newbie_turns > 0) {

				$damage_msg[] = '<span style="color:yellow;">'.$curr_attacker->getPlayerName().'</span> fires a '.$weapon_name.' at debris that once was <span style="color:yellow;">'.$curr_defender->getPlayerName().'</span>.';
				continue;

			}
			if ($curr_defender->sector_id != $curr_attacker->sector_id && $curr_attacker->newbie_turns == 0) {

				$damage_msg[] = '<span style="color:yellow;">'.$curr_attacker->getPlayerName().'</span> fires a '.$weapon_name.' at <span style="color:yellow;">'.$curr_defender->getPlayerName().'</span> as he flees the sector.';
				continue;
			}
			if ($curr_defender->isLandedOnPlanet()) {

				$damage_msg[] = '<span style="color:yellow;">'.$curr_attacker->getPlayerName().'</span> fires a '.$weapon_name.' at <span style="color:yellow;">'.$curr_defender->getPlayerName().'</span> as he flees the sector.';
				continue;
			}
			if ($curr_defender->isFedProtected()) {

				$damage_msg[] = '<span style="color:yellow;">'.$curr_attacker->getPlayerName().'</span> fires a '.$weapon_name.' at <span style="color:yellow;">'.$curr_defender->getPlayerName().'</span> as he flees to the protection of the federal government.';
				continue;
			}
			if ($curr_attacker->isFedProtected()) {

				$damage_msg[] = '<span style="color:yellow;">'.$curr_attacker->getPlayerName().'</span> prepares to fire a '.$weapon_name.' at <span style="color:yellow;">'.$curr_defender->getPlayerName().'</span> but then notices the proximity of the federal ships.';
				continue;
			}

			// create ship object for current defender
			$curr_defender_ship =& SmrShip::getShip($curr_defender->getGameID(),$curr_defender->getAccountID());

			// calc accuracy for this weapon
			$hit = round(($accuracy + $curr_attacker->level_id) - ($curr_defender->level_id / 2));

			// did we hit with this weapon?
			if (mt_rand(0, 100) < $hit) {

				// does the defender has shields?
				if ($curr_defender_ship->getShields() > 0) {

					if ($shield_damage > 0) {

						// do we do more damage than shields left?
						if ($shield_damage > $curr_defender_ship->getShields())
							$shield_damage = $curr_defender_ship->getShields();

						// accumulate the attacker_damage
						$attacker_damage += $shield_damage;

						// subtract the shield damage
						$curr_defender_ship->decreaseShields($shield_damage);

						// text
						$damage_msg[] = '<span style="color:yellow;">'.$curr_attacker->getPlayerName().'</span> fires a '.$weapon_name.' at <span style="color:yellow;">'.$curr_defender->getPlayerName().'</span> and destroys <span style="color:red;">'.$shield_damage.'</span> shields.';

					} elseif ($armor_damage > 0)
						$damage_msg[] = '<span style="color:yellow;">'.$curr_attacker->getPlayerName().'</span> fires a '.$weapon_name.' at <span style="color:yellow;">'.$curr_defender->getPlayerName().'</span> dispersing at the shields.';

				// does the defender have drones?
				} elseif ($curr_defender_ship->getCDs() > 0) {

					if ($armor_damage > 0) {

						// do we do more damage than armor left?
						if ($armor_damage > $curr_defender_ship->getCDs() * 3)
							$armor_damage = $curr_defender_ship->getCDs() * 3;

						// accumulate the attacker_damage
						$attacker_damage += $armor_damage;

						// subtract the armor damage
						$curr_defender_ship->getCDs(round($armor_damage / 3));

						// echo message
						$damage_msg[] = '<span style="color:yellow;">'.$curr_attacker->getPlayerName().'</span> fires a '.$weapon_name.' at <span style="color:yellow;">'.$curr_defender->getPlayerName().'</span> destroying <span style="color:red;">' . round($armor_damage / 3) . '</span> drones.';

					} elseif ($shield_damage > 0)
						$damage_msg[] = '<span style="color:yellow;">'.$curr_attacker->getPlayerName().'</span> fires a '.$weapon_name.' at <span style="color:yellow;">'.$curr_defender->getPlayerName().'</span> which proves to be ineffective against the drones.';

				// does the defender has armor?
				} elseif ($curr_defender_ship->getArmour() > 0) {

					if ($armor_damage > 0) {

						// do we do more damage than armor left?
						if ($armor_damage > $curr_defender_ship->getArmour())
							$armor_damage = $curr_defender_ship->getArmour();

						// accumulate the attacker_damage
						$attacker_damage += $armor_damage;

						// subtract the armor damage
						$curr_defender_ship->decreaseArmour($armor_damage);

						// text
						$damage_msg[] = '<span style="color:yellow;">'.$curr_attacker->getPlayerName().'</span> fires a '.$weapon_name.' at <span style="color:yellow;">'.$curr_defender->getPlayerName().'</span> destroying <span style="color:red;">'.$armor_damage.'</span> plates of armor.';

					} elseif ($shield_damage > 0)
						$damage_msg[] = '<span style="color:yellow;">'.$curr_attacker->getPlayerName().'</span> fires a '.$weapon_name.' at <span style="color:yellow;">'.$curr_defender->getPlayerName().'</span> which proves to be ineffective against the hull.';
				}

				// is there something to update?
				if ($attacker_damage > 0)
					$curr_defender_ship->update_hardware();

				// is the defender dead?
				if ($curr_defender_ship->getShields() == 0 && $curr_defender_ship->getArmour() == 0) {

					// do urgent things now
					$curr_defender->set_info('dead', 'TRUE');
					//$damage_msg[] = '<br><font color=red>DEBUG: SET DEAD</font><br>';
					// make an entry in the 'kill' table
					$db->lock('kills');
					$db->query('SELECT * FROM kills ' .
							   'WHERE game_id = '.$curr_defender->getGameID().' AND ' .
									 'dead_id = '.$curr_defender->getAccountID());
					//$damage_msg[] = '<br><font color=red>DEBUG: SELECT processed FROM kills WHERE game_id = $curr_defender->getGameID() AND dead_id = $curr_defender->getAccountID()</font><br>';

					if (!$db->next_record()) {

						$db->query('INSERT INTO kills (game_id, dead_id, killer_id, dead_exp, kill_exp, sector_id, time) ' .
								   'VALUES('.$curr_defender->getGameID().', '.$curr_defender->getAccountID().', '.$curr_attacker->getAccountID().', '.$curr_defender->getExperience().', '.$curr_attacker->getExperience().', '.$this_sector.', ' . TIME . ')');
						//$damage_msg[] = '<br><font color=red>DEBUG: INSERT INTO kills (game_id, dead_id, killer_id, dead_exp, kill_exp, sector_id, time, processed) VALUES($curr_defender->getGameID(), $curr_defender->getAccountID(), $curr_attacker->getAccountID(), $curr_defender->getExperience(), $curr_attacker->getExperience(), $this_sector, ' . time() . ', 'FALSE')</font><br>';
						// echo messages
						$damage_msg[] = '<span style="color:yellow;">'.$curr_defender->getPlayerName().'</span> is <span style="color:red;">DESTROYED!</span>';
						$damage_msg[] = '<span style="color:yellow;">'.$curr_attacker->getPlayerName().'</span> claims <span style="color:yellow;">'.$curr_defender->getPlayerName().'</span> credits amounting to ' . number_format($curr_defender->credits) . '.';

					}
					$db->unlock();
				}

			} else
				$damage_msg[] = '<span style="color:yellow;">'.$curr_attacker->getPlayerName().'</span> fires a $weapon_name at <span style="color:yellow;">'.$curr_defender->getPlayerName().'</span> and misses.';

		} // end of weapons

		// get defender for drones here incase they don't have drones (for the dead check)
		$curr_defender =& SmrPlayer::getPlayer($defender_team->next(false), $curr_attacker->getGameID());

		// create ship object for current defender
		$curr_defender_ship =& SmrShip::getShip($curr_defender->getGameID(),$curr_defender->getAccountID());
		$continue_drones = 'yes';
		if ($curr_defender->isFedProtected() && $curr_attacker_ship->getCDs() > 0) {

			$damage_msg[] = '<span style="color:yellow;">'.$curr_attacker->getPlayerName().'</span> launches drones at <span style="color:yellow;">'.$curr_defender->getPlayerName().'</span> as he flees to the protection of the federal government.';
			$continue_drones = 'no';

		}
		if ($curr_defender->newbie_turns > 0 && $curr_attacker_ship->getCDs() > 0) {

			$damage_msg[] = '<span style="color:yellow;">'.$curr_attacker->getPlayerName().'</span> launches drones at the debris that once was <span style="color:yellow;">'.$curr_defender->getPlayerName().'</span>.';
			$continue_drones = 'no';

		}
		if ($curr_defender->sector_id != $curr_attacker->sector_id && $curr_attacker->newbie_turns == 0 && $curr_attacker_ship->getCDs() > 0) {

			$damage_msg[] = '<span style="color:yellow;">'.$curr_attacker->getPlayerName().'</span> launches drones at <span style="color:yellow;">'.$curr_defender->getPlayerName().'</span> as he flees the sector.';
			$continue_drones = 'no';
		}
		if ($curr_defender->isLandedOnPlanet() && $curr_attacker_ship->getCDs() > 0) {

			$damage_msg[] = '<span style="color:yellow;">'.$curr_attacker->getPlayerName().'</span> launches drones at <span style="color:yellow;">'.$curr_defender->getPlayerName().'</span> as he flees the sector.';
			$continue_drones = 'no';
		}
		if ($curr_attacker->isFedProtected()) {

			$damage_msg[] = '<span style="color:yellow;">'.$curr_attacker->getPlayerName().'</span> prepares to launch drones at <span style="color:yellow;">'.$curr_defender->getPlayerName().'</span> but then notices the proximity of the federal ships.';
			$continue_drones = 'no';
		}
		// do we have drones?
		if ($curr_attacker_ship->getCDs() > 0 && $curr_defender->dead == 'FALSE' && $continue_drones == 'yes') {

			// Random(3 to 54) + Random(Attacker level / 4 to Attacker level) - (Defender Level - Attacker Level /3)
			 $number_attacking_percent = ((mt_rand(3, 54) + mt_rand($curr_attacker->level_id / 4, $curr_attacker->level_id)) - ($curr_defender->level_id - $curr_attacker->level_id)/3) / 100;

			// that one doesn't seem to be accepted
			//% firing = (rand(attacker_level,50) + attacker_level) - rand(defender_level / 2, defender_level)
			//$number_attacking_percent = ((rand($curr_attacker->level_id, 50) + $curr_attacker->level_id) - rand($curr_defender->level_id / 2, $curr_defender->level_id)) / 100;

			if ($number_attacking_percent < 0) $number_attacking_percent = 0;

			// with the percentage we calc how many CD actually attack
			$number_attacking = round($number_attacking_percent * $curr_attacker_ship->getCDs());

			// more than we have?
			if ($number_attacking > $curr_attacker_ship->getCDs())
				$number_attacking = $curr_attacker_ship->getCDs();

			// DCS may modify that
			if ($curr_defender_ship->hasDCS() == 1)
				$drone_mod = 1.5;
			else
				$drone_mod = 2;

			$att_dis = $number_attacking;

			// are there shields left?
			//reset this variable for this attacker so we don't display the other attackers dmg
			$killed_shields = 0;
			if ($curr_defender_ship->getShields() > 0) {

				// assume all drones are killing shields
				$number_shields = $number_attacking;

				// can we do more damage than shields left?
				if ($number_shields * $drone_mod > $curr_defender_ship->getShields())
					$number_shields = ceil($curr_defender_ship->getShields() / $drone_mod);

				// subtract the number of drones that hit for shields
				// from the total number of attacking drones
				$number_attacking -= $number_shields;

				// how many shields can we kill with that drones?
				$killed_shields = ceil($number_shields * $drone_mod);

				// maybe have to correct this value?
				if ($killed_shields > $curr_defender_ship->getShields())
					$killed_shields = $curr_defender_ship->getShields();

				// take shields
				$curr_defender_ship->decreaseShields($killed_shields);

				// accumulate attacker damage
				$attacker_damage += $killed_shields;

			}

			//reset this variable for this attacker so we don't display the other attackers dmg
			$killed_drones = 0;
			// are there drones left?
			if ($curr_defender_ship->getCDs() > 0 && $number_attacking > 0) {

				// assume all drones are killing drones
				$number_drones = $number_attacking;

				// can we do more damage than drones left?
				if ($number_drones * $drone_mod > $curr_defender_ship->getCDs() * 3)
					$number_drones = ceil($curr_defender_ship->getCDs() * 3 / $drone_mod);

				// subtract the number of drones that hit for drones
				// from the total number of attacking drones
				$number_attacking -= $number_drones;

				// how many drones can we kill with that drones?
				$killed_drones = round($number_drones * $drone_mod / 3);

				// maybe have to correct this value?
				if ($killed_drones > $curr_defender_ship->getCDs())
					$killed_drones = $curr_defender_ship->getCDs();

				// take drones
				$curr_defender_ship->decreaseCDs($killed_drones);

				// accumulate attacker damage
				$attacker_damage += ($killed_drones * 3);

			}

			// are there armor left?
			if ($curr_defender_ship->getArmour() > 0 && $number_attacking > 0) {

				// assume all drones are killing armor
				$number_armor = $number_attacking;

				// can we do more damage than armor left?
				if ($number_armor * $drone_mod > $curr_defender_ship->getArmour())
					$number_armor = ceil($curr_defender_ship->getArmour() / $drone_mod);

				// subtract the number of drones that hit for armor
				// from the total number of attacking drones
				$number_attacking -= $number_armor;

				// how many armor can we kill with that drones?
				$killed_armor = ceil($number_armor * $drone_mod);

				// maybe have to correct this value?
				if ($killed_armor > $curr_defender_ship->getArmour())
					$killed_armor = $curr_defender_ship->getArmour();

				// take armor
				$curr_defender_ship->decreaseArmour($killed_armor);

				// accumulate attacker damage
				$attacker_damage += $killed_armor;

			}

			// make changes permanent
			$curr_defender_ship->update_hardware();

			// build text
			$msg = '<span style="color:yellow;">'.$curr_attacker->getPlayerName().'</span> launches ' . $att_dis . ' drones hitting <span style="color:yellow;">'.$curr_defender->getPlayerName().'</span> and destroying ';

			if ($killed_shields > 0) {

				if ($killed_shields == 1)
					$msg .= '<span style="color:red;">1</span> shield';
				else
					$msg .= '<span style="color:red;">'.$killed_shields.'</span> shields';

				if ($killed_drones > 0 && $killed_armor > 0)
					$msg .= ', ';
				elseif ($killed_drones > 0 || $killed_armor > 0)
					$msg .= ' and ';

			}

			if ($killed_drones > 0) {

				if ($killed_drones == 1)
					$msg .= '<span style="color:red;">1</span> drone';
				else
					$msg .= '<span style="color:red;">'.$killed_drones.'</span> drones';

				if ($killed_armor > 0)
					$msg .= ' and ';

			}

			if ($killed_armor > 0) {

				if ($killed_armor == 1)
					$msg .= '<span style="color:red;">1</span> plate of armor';
				else
					$msg .= '<span style="color:red;">'.$killed_armor.'</span> plates of armor';

			}

			// add this to the outgoing message array
			$damage_msg[] = $msg . '.';

		} // end of 'do we have drones?'
		elseif ($curr_attacker_ship->getCDs() > 0 && $continue_drones == 'yes')
			$damage_msg[] = '<span style="color:yellow;">'.$curr_attacker->getPlayerName().'</span> launches it combat drones at the debris field that turned out to be <span style="color:yellow;">'.$curr_defender->getPlayerName().'</span>.';

		// is the defender dead?
		if ($curr_defender_ship->getShields() == 0 && $curr_defender_ship->getArmour() == 0 && $curr_defender->dead == 'FALSE') {

			// do urgent things now
			$curr_defender->set_info('dead', 'TRUE');
			//$damage_msg[] = '<br><font color=red>DEBUG: SET DEAD</font><br>';
			// make an entry in the 'kill' table
			$db->lock('kills');
			$db->query('SELECT * FROM kills ' .
					   'WHERE game_id = '.$curr_defender->getGameID().' AND ' .
							 'dead_id = '.$curr_defender->getAccountID());
			//$damage_msg[] = '<br><font color=red>DEBUG: SELECT processed FROM kills WHERE game_id = $curr_defender->getGameID() AND dead_id = $curr_defender->getAccountID()</font><br>';
			if (!$db->next_record()) {

				$db->query('INSERT INTO kills (game_id, dead_id, killer_id, dead_exp, kill_exp, sector_id, time) ' .
						   'VALUES('.$curr_defender->getGameID().', '.$curr_defender->getAccountID().', '.$curr_attacker->getAccountID().', '.$curr_defender->getExperience().', '.$curr_attacker->getExperience().', '.$this_sector.',' . time() . ')');
				//$damage_msg[] = '<br><font color=red>DEBUG: INSERT INTO kills (game_id, dead_id, killer_id, dead_exp, kill_exp, sector_id, time, processed) VALUES($curr_defender->getGameID(), $curr_defender->getAccountID(), $curr_attacker->getAccountID(), $curr_defender->getExperience(), $curr_attacker->getExperience(), $this_sector, ' . time() . ', 'FALSE')</font><br>';
				// echo messages
				$damage_msg[] = '<span style="color:yellow;">'.$curr_defender->getPlayerName().'</span> is <span style="color:red;">DESTROYED!</span>';
				$damage_msg[] = '<span style="color:yellow;">'.$curr_attacker->getPlayerName().'</span> claims <span style="color:yellow;">'.$curr_defender->getPlayerName().'</span> credits amounting to ' . number_format($curr_defender->credits) . '.';

			}
			$db->unlock();
		}

		// echo the overall damage
		if ($attacker_damage > 0) {

			// text
			$damage_msg[] = '<span style="color:yellow;">'.$curr_attacker->getPlayerName().'</span> does a total of <span style="color:red;">'.$attacker_damage.'</span> damage.';

			// 25% of the damage goes to xp
			$curr_attacker->increaseExperience($attacker_damage / 4);
			$curr_attacker->set_info('experience', $curr_attacker->getExperience());

		} else
			$damage_msg[] = '<span style="color:yellow;">'.$curr_attacker->getPlayerName().'</span> does absolutely no damage this round. Send the worthless lout back to the academy!';

		$attacker_team_damage += $attacker_damage;
		$curr_attacker->update_stat('player_damage', $attacker_damage);

		$damage_msgs[] = $damage_msg;

	}

	// echo the overall damage
	$team_msg = array();
	if ($attacker_team_damage > 0)
		$team_msg[] = '<br>This team does a total of <span style="color:red;">'.$attacker_team_damage.'</span> damage in this round of combat.';
	else
		$team_msg[] = '<br>This team does no damage at all. You call that a team? They need a better recruiter.';
	$damage_msgs[] = $team_msg;
	return $damage_msgs;

}

?>