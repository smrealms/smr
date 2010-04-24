<?php

// include file with database class
//require_once(LIB . "Smr12MySqlDatabase.inc");
require_once(get_file_loc('Smr12MySqlDatabase.class.inc'));

class SMR_SHIP extends Smr12MySqlDatabase {

	var $account_id;
	var $game_id;
	var $ship_name;
	var $ship_type_id;

	var $hardware;
	var $max_hardware;
	var $old_hardware;

	var $cargo;
	var $cargo_left;

	var $speed;
	var $cost;

	var $hardpoint;
	var $weapon;
	var $weapon_open;
	var $weapon_used;
	
	var $player_exp;
	var $player_level;


	function SMR_SHIP($account_id, $game_id) {

		$this->account_id = $account_id;
		$this->game_id = $game_id;

		$this->load_ship();
		$this->load_hardware();
		$this->load_weapon();
		$this->load_cargo();

	}

	function load_ship() {

		// determine ship
		$this->query("SELECT * FROM player, ship_type WHERE player.ship_type_id = ship_type.ship_type_id AND " .
															"account_id = $this->account_id AND " .
															"game_id = $this->game_id LIMIT 1");
		$this->next_record();
		$this->ship_name			= stripslashes($this->f("ship_name"));
		$this->ship_type_id			= $this->f("ship_type_id");
		$this->hardpoint			= $this->f("hardpoint");
		$this->speed				= $this->f("speed");
		$this->cost					= $this->f("cost");
		$this->player_exp			= $this->f("experience");
		$this->query("SELECT * FROM level WHERE requirement <= $this->player_exp ORDER BY level_id DESC LIMIT 1");
		if ($this->next_record())
			$this->player_level		= $this->f("level_id");
		else
			$this->player_level		= 1;

	}

	function load_weapon() {

		// determine weapon
		$this->query("SELECT order_id,weapon_name FROM ship_has_weapon, weapon_type WHERE ship_has_weapon.weapon_type_id = weapon_type.weapon_type_id AND " .
																	  "account_id = $this->account_id AND " .
																	  "game_id = $this->game_id " .
																	"ORDER BY order_id");
		$this->weapon_used = $this->nf();
		$this->weapon_open = $this->hardpoint - $this->weapon_used;

		$this->weapon = array();

		// generate list of weapon names the user transports
		while ($this->next_record()) {

			$order_id		= $this->f("order_id");
			$weapon_name	= $this->f("weapon_name");

			$this->weapon[$order_id] = $weapon_name;

		}
	}

	function load_cargo() {

		// initialize cargo array
		$this->cargo = array();
		$this->cargo_left = $this->hardware[3];

		// get cargo from db
		$this->query("SELECT * FROM ship_has_cargo, good WHERE ship_has_cargo.good_id = good.good_id AND " .
																		"account_id = $this->account_id AND " .
																		"game_id = $this->game_id");
		while ($this->next_record()) {

			$good_id	= $this->f("good_id");
			$amount		= $this->f("amount");

			// subtract the current amount from cargo_hold to get cargo left value
			$this->cargo_left -= $amount;

			// adding cargo and amount to array
			$this->cargo[$good_id] = $amount;
		}

		// initialize empty cargo
		$this->query("SELECT good_id FROM good");
		while ($this->next_record()) {

			$good_id = $this->f("good_id");
			if (empty($this->cargo[$good_id])) $this->cargo[$good_id] = 0;
		}
	}

	function load_hardware() {

		$this->hardware = array();
		$this->max_hardware = array();

		// get currently hardware from db
		$this->query("SELECT * FROM ship_has_hardware, hardware_type " .
							  "WHERE account_id = $this->account_id AND " .
									"game_id = $this->game_id AND " .
									"ship_has_hardware.hardware_type_id = hardware_type.hardware_type_id");

		while ($this->next_record()) {

			$hardware_type_id = $this->f("hardware_type_id");

			// adding hardware to array
			$this->hardware[$hardware_type_id]		= $this->f("amount");
			$this->old_hardware[$hardware_type_id] = $this->f('old_amount');

		}
		// get supported hardware from db
		$this->query("SELECT hardware_type.hardware_type_id as hardware_type_id, max_amount FROM ship_type_support_hardware, hardware_type " .
							  "WHERE ship_type_support_hardware.hardware_type_id = hardware_type.hardware_type_id AND " .
									  "ship_type_id = $this->ship_type_id");

		while ($this->next_record()) {

			$hardware_type_id	= $this->f("hardware_type_id");

			// adding hardware to array
			$this->max_hardware[$hardware_type_id] = $this->f("max_amount");
		}

		// initialize empty hardware
		$this->query("SELECT hardware_type_id FROM hardware_type");
		while ($this->next_record()) {

			$hardware_type_id = $this->f("hardware_type_id");

			// initialize empty hardware
			if (empty($this->hardware[$hardware_type_id])) $this->hardware[$hardware_type_id] = 0;
			if (empty($this->max_hardware[$hardware_type_id])) $this->max_hardware[$hardware_type_id] = 0;
		}

	}

	function attack_rating() {

		// for rating
		$this->query("SELECT sum(shield_damage), sum(armor_damage) FROM ship_has_weapon, weapon_type " .
																  "WHERE ship_has_weapon.weapon_type_id = weapon_type.weapon_type_id AND " .
																		"account_id = $this->account_id AND " .
																		"game_id = $this->game_id");
		$this->next_record();
		$sum_shield_damage = $this->f("sum(shield_damage)");
		$sum_armor_damage = $this->f("sum(armor_damage)");
		$maxDronesPercent = (35 + $this->player_level * .6 + ($this->player_level - 1) * .4 + 15) * .01;
		$maxDrones = $maxDronesPercent * $this->hardware[4];
		return round((($sum_shield_damage + $sum_armor_damage + $maxDrones * 2) / 40));
	}

	function defense_rating() {
		return round((($this->hardware[1] + $this->hardware[2]) / 100) + (($this->hardware[4] * 3) / 100));
	}

	function update_cargo() {

		$this->cargo_left = $this->hardware[3];

		// write cargo info
		foreach ($this->cargo as $id => $amount) {

			$this->cargo_left -= $amount;

			if ($amount != 0)
				$this->query("REPLACE INTO ship_has_cargo (account_id, game_id, good_id, amount) VALUES($this->account_id, $this->game_id, $id, $amount)");
			else
				$this->query("DELETE FROM ship_has_cargo WHERE account_id = $this->account_id AND game_id = $this->game_id AND good_id = $id LIMIT 1");
		}
	}

	function update_hardware() {

		$this->lock("ship_has_hardware");

		// write hardware info
		foreach ($this->hardware as $hardware_type_id => $amount) {

			if ($amount > 0) {

				$this->query("SELECT * FROM ship_has_hardware WHERE account_id = $this->account_id AND game_id = $this->game_id and hardware_type_id = $hardware_type_id");
				if ($this->next_record()) {

					$this->query("UPDATE ship_has_hardware SET amount = $amount WHERE account_id = $this->account_id AND game_id = $this->game_id and hardware_type_id = $hardware_type_id");

				} else {

					$this->query("INSERT INTO ship_has_hardware (account_id, game_id, hardware_type_id, amount, old_amount) VALUES($this->account_id, $this->game_id, $hardware_type_id, $amount, $amount)");

				}

			} else
				$this->query("DELETE FROM ship_has_hardware WHERE account_id = $this->account_id AND game_id = $this->game_id AND hardware_type_id = $hardware_type_id");

		}

		$this->unlock();

	}

	function update_weapon() {

		// write weapon info
		$this->query("DELETE FROM ship_has_weapon " .
							"WHERE account_id = $this->account_id AND " .
								  "game_id = $this->game_id");
		$count = 1;
		foreach ($this->weapon as $order_id => $weapon_name) {

			// get the weapon_id first
			$this->query("SELECT * FROM weapon_type WHERE weapon_name = '$weapon_name'");
			$this->next_record();
			$weapon_type_id = $this->f("weapon_type_id");

			$this->query("INSERT INTO ship_has_weapon (account_id, game_id, order_id, weapon_type_id) " .
									  "VALUES($this->account_id, $this->game_id, $count, $weapon_type_id)");
			$count++;
		}
	}

	function update() {

		// write ship infos
		$this->query("UPDATE player SET ship_type_id = $this->ship_type_id " .
								   "WHERE account_id = $this->account_id AND " .
										 "game_id = $this->game_id");

	}

	function shield_low() { return floor($this->hardware[1] / 100) * 100; }
	function shield_high() { return $this->shield_low() + 100; }
	function armor_low() { return floor($this->hardware[2] / 100) * 100; }
	function armor_high() { return $this->armor_low() + 100; }
	function combat_drones_low() { return floor($this->hardware[4] / 100) * 100; }
	function combat_drones_high() { return $this->combat_drones_low() + 100; }

	function check_power_level($power_level) {

		$this->query("SELECT ship_has_weapon.weapon_type_id FROM ship_has_weapon, weapon_type " .
							  "WHERE ship_has_weapon.weapon_type_id = weapon_type.weapon_type_id AND " .
									"account_id = $this->account_id AND " .
									"game_id = $this->game_id AND " .
									"power_level = $power_level");

		switch ($power_level) {

			case 5: if ($this->nf() < 1) return 1; else return 0;
					break;
			case 4: if ($this->nf() < 2) return 1; else return 0;
					break;
			case 3: if ($this->nf() < 3) return 1; else return 0;
					break;
			case 2: if ($this->nf() < 4) return 1; else return 0;
					break;
			case 1: return 1;
					break;
		}
		return 1;
	}

	function add_weapon($weapon_type_id) {

		// get the weapon_name first
		$this->query("SELECT * FROM weapon_type WHERE weapon_type_id = $weapon_type_id");
		if ($this->next_record()) {

			array_push($this->weapon, $this->f("weapon_name"));
			$this->update_weapon();

		}

	}

	function remove_weapon($weapon_type_id) {

		$this->query("DELETE FROM ship_has_weapon WHERE account_id = $this->account_id AND " .
														"game_id = $this->game_id AND " .
														"weapon_type_id = $weapon_type_id");

	}

	function remove_all_weapon() {

		$this->weapon = array();
		$this->query("DELETE FROM ship_has_weapon WHERE account_id = $this->account_id AND " .
														"game_id = $this->game_id");

	}

	function remove_all_cargo() {

		$this->cargo = array();
		$this->query("DELETE FROM ship_has_cargo WHERE account_id = $this->account_id AND " .
													  "game_id = $this->game_id");

	}

	function remove_all_hardware() {

		$this->hardware = array();
		$this->query("DELETE FROM ship_has_hardware WHERE account_id = $this->account_id AND " .
														 "game_id = $this->game_id");
		$this->query("DELETE FROM ship_has_illusion WHERE account_id = $this->account_id AND " .
														 "game_id = $this->game_id");
		$this->query("DELETE FROM ship_is_cloaked WHERE account_id = $this->account_id AND " .
														"game_id = $this->game_id");

	}

	function get_pod() {

		$this->remove_all_weapon();
		$this->remove_all_cargo();
		$this->remove_all_hardware();

		$this->hardware[1] = 50;
		$this->hardware[2] = 50;
		$this->hardware[3] = 5;
		$this->update_hardware();

		$this->ship_type_id = 69;
		$this->update();

		$this->mark_seen();

	}

	function mark_seen() {
		foreach ($this->hardware as $hardware_type_id => $amount) {
			$this->query("UPDATE ship_has_hardware SET old_amount = $amount " .
							 "WHERE account_id = $this->account_id AND " .
							   "game_id = $this->game_id AND " .
								   "hardware_type_id = $hardware_type_id LIMIT 1");
		}
	}

	function cloak_active() {

		$this->query("SELECT * FROM ship_is_cloaked " .
					 "WHERE account_id = $this->account_id AND " .
					  "game_id = $this->game_id LIMIT 1");
			return $this->nf();

	}

	function disable_cloak() {
			$this->query("DELETE FROM ship_is_cloaked WHERE account_id = $this->account_id AND " .
														"game_id = $this->game_id");
	}

	function enable_cloak() {

		$this->query("REPLACE INTO ship_is_cloaked VALUES($this->account_id, $this->game_id)");

	}

	function cloak_overload() {
		// 1 in 25 chance of cloak being destroyed if active
		if($this->cloak_active() && mt_rand(0,99) < 5) {
			$this->query(
				'DELETE FROM ship_has_hardware ' .
				'WHERE account_id=' . $this->account_id . ' ' .
				'AND game_id=' . $this->game_id . ' ' .
				'AND hardware_type_id=8 ' .
				'limit 1'
			);
			$this->disable_cloak();
			$this->hardware[8] = 0;
			return true;
		}	
		
		return false;
		
	}
	
	function set_illusion($ship_id, $attack, $defense) {

		$this->query("REPLACE INTO ship_has_illusion VALUES($this->account_id, $this->game_id, $ship_id, $attack, $defense)");

	}

	function disable_illusion() {

		$this->query("DELETE FROM ship_has_illusion WHERE account_id = $this->account_id AND game_id = $this->game_id LIMIT 1");

	}

	function get_illusion() {

		$this->query("SELECT ship_type_id FROM ship_has_illusion WHERE account_id = $this->account_id AND " .
		"game_id = $this->game_id LIMIT 1");
		$this->next_record();
		return $this->f("ship_type_id");

	}

	function get_illusion_attack() {

		$this->query("SELECT attack FROM ship_has_illusion WHERE account_id = $this->account_id AND " .
															"game_id = $this->game_id LIMIT 1");
		$this->next_record();
		return $this->f("attack");

	}

	function get_illusion_defense() {

		$this->query("SELECT defense FROM ship_has_illusion WHERE account_id = $this->account_id AND " .
															"game_id = $this->game_id LIMIT 1");
		$this->next_record();
		return $this->f("defense");

	}

	function under_attack($hardware_type_id) {

		$this->query("SELECT * FROM ship_has_hardware WHERE account_id = $this->account_id AND game_id = $this->game_id AND hardware_type_id = $hardware_type_id AND amount != old_amount LIMIT 1");
		return $this->nf();

	}

}

?>