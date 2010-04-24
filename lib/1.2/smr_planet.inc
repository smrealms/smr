<?php

// include file with database class
//require_once(LIB . "SmrMySqlDatabase.inc");
require_once(get_file_loc('SmrMySqlDatabase.class.inc'));

class SMR_PLANET extends SmrMySqlDatabase {

	var $sector_id;
	var $game_id;
	var $planet_name;
	var $owner_id;
	var $password;
	var $shields;
	var $drones;
	var $credits;
	var $bonds;
	var $maturity;
	var $stockpile;
	var $construction;
	var $max_construction;
	var $inhabitable_time;
	var $level;
	var $current_building_id;
	var $current_building_name;
	var $time_left;


	function SMR_PLANET($curr_sector_id, $game_id) {

		if (empty($curr_sector_id)) {

			print("This planet doesn't exist!");
			return;

		}

		$this->sector_id = $curr_sector_id;
		$this->game_id = $game_id;

		$this->query("SELECT * FROM planet WHERE game_id = $this->game_id AND " .
												"sector_id = $this->sector_id");
		if ($this->next_record()) {

			$this->planet_name		= stripslashes($this->f("planet_name"));
			$this->owner_id			= $this->f("owner_id");
			$this->password			= $this->f("password");
			$this->shields			= $this->f("shields");
			$this->drones			= $this->f("drones");
			$this->credits			= $this->f("credits");
			$this->bonds			= $this->f("bonds");
			$this->maturity			= $this->f("maturity");
			$this->inhabitable_time = $this->f("inhabitable_time");

		}

		// initialize cargo array
		$this->stockpile = array();
		$this->level = $this->level();
		// get supplies from db
		$this->query("SELECT * FROM planet_has_cargo, good WHERE planet_has_cargo.good_id = good.good_id AND " .
																"game_id = $this->game_id AND " .
																"sector_id = $this->sector_id");
		// adding cargo and amount to array
		while ($this->next_record())
			$this->stockpile[$this->f("good_id")] = $this->f("amount");

		// initialize empty cargo
		$this->query("SELECT * FROM good");
		while ($this->next_record()) {

			$good_id = $this->f("good_id");
			if (empty($this->stockpile[$good_id]))
				$this->stockpile[$good_id] = 0;

		}

		$this->construction = array();

		// get constructions from db
		$this->query("SELECT * FROM planet_has_construction WHERE game_id = $this->game_id AND " .
																 "sector_id = $this->sector_id");
		// adding building and amount to array
		while ($this->next_record())
			$this->construction[$this->f("construction_id")] = $this->f("amount");

		$this->max_construction = array();

		// get constructions from db
		$this->query("SELECT * FROM planet_construction");
		while ($this->next_record()) {

			$construction_id	= $this->f("construction_id");
			$max_construction	= $this->f("max_construction");

			$this->max_construction[$construction_id] = $max_construction;

			if (empty($this->max_construction[$construction_id]))
				$this->max_construction[$construction_id] = 0;
			if (empty($this->construction[$construction_id]))
				$this->construction[$construction_id] = 0;

		}
	}

	function update() {

		$this->query("UPDATE planet SET owner_id = $this->owner_id, " .
										"password = '$this->password', " .
										"planet_name = " . format_string($this->planet_name, TRUE) . ", " .
										"shields = $this->shields, " .
										"drones = $this->drones, " .
										"credits = $this->credits, " .
										"bonds = $this->bonds, " .
										"maturity = $this->maturity " .
									"WHERE game_id = $this->game_id AND " .
										  "sector_id = $this->sector_id");

		// write stockpile info
		foreach ($this->stockpile as $id => $amount) {

			if ($amount != 0)
				$this->query("REPLACE INTO planet_has_cargo (game_id, sector_id, good_id, amount) " .
													 "VALUES($this->game_id, $this->sector_id, $id, $amount)");
			else
				$this->query("DELETE FROM planet_has_cargo WHERE game_id = $this->game_id AND " .
																"sector_id = $this->sector_id AND " .
																"good_id = $id");
		}

		// write building info
		foreach ($this->construction as $id => $amount) {

			if ($amount != 0)
				$this->query("REPLACE INTO planet_has_construction (game_id, sector_id, construction_id, amount) " .
															"VALUES($this->game_id, $this->sector_id, $id, $amount)");
			else
				$this->query("DELETE FROM planet_has_construction WHERE game_id = $this->game_id AND " .
																	   "sector_id = $this->sector_id AND " .
																	   "construction_id = $id");
		}
	}

	function level() {

		return number_format(($this->construction[1] +
							  $this->construction[2] +
							  $this->construction[3]) / 3, 2);

	}

	function accuracy() {

		return round(25 + $this->level());

	}

	function stockpile_left($id) {

		return 600 - $this->stockpile[$id];

	}

	/**
	 * Returns true if there is a construction in building
	 */
	function build() {

		$this->query("SELECT * FROM planet_build_construction, planet_construction " .
							 "WHERE planet_build_construction.construction_id = planet_construction.construction_id AND " .
							 	   "game_id = $this->game_id AND " .
								   "sector_id = $this->sector_id");
		if ($this->next_record()) {

			$this->current_building_id		= $this->f("construction_id");
			$this->current_building_name	= $this->f("construction_name");
			$this->time_left				= $this->f("time_complete") - time();

			// is the construction ready
			if ($this->time_left < 0) {

				$this->query("DELETE FROM planet_build_construction WHERE game_id = $this->game_id AND " .
																		 "sector_id = $this->sector_id");
				$this->construction[$this->current_building_id] += 1;
				$this->update();
				return false;

			}

			return true;

		} else
			return false;

	}

	function start_construction($construction_id) {

		// take the goods that are needed
		$this->query("SELECT * FROM planet_cost_good, good " .
							  "WHERE planet_cost_good.good_id = good.good_id AND " .
									"construction_id = $construction_id " .
							  "ORDER BY good.good_id");
		while ($this->next_record()) {

			$good_id	= $this->f("good_id");
			$amount		= $this->f("amount");

			$this->stockpile[$good_id] -= $amount;
		}

		$this->update();

		// get game speed
		$this->query("SELECT * FROM game WHERE game_id = $this->game_id");
		if ($this->next_record())
			$game_speed = $this->f("game_speed");

		// gets the time for the construction
		$this->query("SELECT * FROM planet_cost_time WHERE construction_id = $construction_id");
		if ($this->next_record())
			$time_complete = time() + round($this->f("amount") / $game_speed);

		$this->query("INSERT INTO planet_build_construction (game_id, sector_id, construction_id, time_complete) " .
													"VALUES ($this->game_id, $this->sector_id, $construction_id, $time_complete)");

	}
}

?>