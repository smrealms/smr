<?php
// TODO: Plenty of room for improvement here
// include file with database class
//require_once(LIB . "Smr12MySqlDatabase.inc");
require_once(get_file_loc('Smr12MySqlDatabase.class.inc'));

class SMR_PLAYER extends Smr12MySqlDatabase {

	var $account_id;
	var $game_id;
	var $player_name;
	var $player_id;
	var $race_name;
	var $race_id;
	var $turns;
	var $newbie_turns;
	var $credits;
	var $experience;
	var $alignment;
	var $level_id;
	var $level_name;
	var $alliance_id;
	var $alliance_name;
	var $alliance_description;
	var $last_turn_update;
	var $sector_id;
	var $military_payment;
	var $last_sector_id;
	var $controlled;
	var $dead;
	var $old_dead;
	var $bank;
	var $land_on_planet;
	var $newbie_warning;
	var $kills;
	var $deaths;
	var $ship_type_id;
	var $relations;
	var $relations_global;
	var $relations_global_rev;
	var $ignore_global;
	var $last_news_update;
	var $display_experience;
	var $display_level_id;
	var $display_level_name;
	var $sector_change;
	var $game_type;
	var $game_speed;
	var $validated;
	var $out_of_game;
	var $last_ticker_update;
	var $ticker;
    var $safe_exit;
    var $detected;
    var $last_active;
 
	function SMR_PLAYER ($account_id, $game_id) {
		global $session;
        parent::__construct();
		$this->account_id = $account_id;
		$this->game_id = $game_id;

		// get some general user infos
		$this->query("SELECT * FROM player WHERE account_id = $account_id AND game_id = $game_id LIMIT 1");

		if ($this->next_record()) {

            $row = $this->fetch_row();          
			$this->player_name		= stripslashes($row['player_name']);
			$this->player_id		= $row['player_id'];
			$this->race_id			= $row['race_id'];
			$this->turns			= $row['turns'];
			$this->newbie_turns		= $row['newbie_turns'];
			$this->credits			= $row['credits'];
			$this->experience		= $row['experience'];
			$this->alignment		= $row['alignment'];
			$this->last_turn_update	= $row['last_turn_update'];
			$this->last_active		= $row['last_active'];
			$this->sector_id		= $row['sector_id'];
			$this->last_sector_id	= $row['last_sector_id'];
			$this->controlled		= $row['controlled'];
			$this->military_payment = $row['military_payment'];
			$this->dead				= $row['dead'];
			$this->old_dead			= $this->dead;
			$this->bank				= $row['bank'];
			$this->land_on_planet	= $row['land_on_planet'];
			$this->ship_type_id		= $row['ship_type_id'];
			$this->newbie_warning	= $row['newbie_warning'];
			$this->kills			= $row['kills'];
			$this->deaths			= $row['deaths'];
			$this->ignore_global	= $row['ignore_global'];
			$this->last_news_update = $row['last_news_update'];
			$this->out_of_game		= $row['out_of_game'];
			$this->last_ticker_update = $row['last_ticker_update'];
			$this->alliance_id			= $row['alliance_id'];
            $this->sector_change = $row['sector_change'];
            $this->safe_exit = $row['safe_exit'];
            $this->detected = $row['detected'];
			$this->query("SELECT race_name FROM race WHERE race_id=" . $this->race_id);
			$this->next_record();
			$this->race_name = $this->f('race_name');
			if (empty($this->kills))
				$this->kills = 0;
			if (empty($this->deaths))
				$this->deaths = 0;

		} else
			throw new Exception('Invalid accountID: '.$account_id . ' OR gameID:'.$game_id);

		// if this user doesn't exist make xp to 0 to prevent sql error
		if (empty($this->experience))
			$this->experience = 0;


		// determine user level
		$this->query("SELECT * FROM level WHERE requirement <= $this->experience " .
										 "ORDER BY requirement DESC LIMIT 1");
		if ($this->next_record()) {

			$this->level_id			= $this->f("level_id");
			$this->level_name		= $this->f("level_name");

		}

		//get ticker info
		$this->query("SELECT type FROM player_has_ticker WHERE account_id = $account_id AND game_id = $game_id AND type != 'block' LIMIT 1");
		if ($this->next_record()) $this->ticker = strtoupper($this->f('type'));
		else $this->ticker = "FALSE";
		
		// get alliance
		$this->query("SELECT alliance_name FROM alliance " .
					 "WHERE alliance_id = $this->alliance_id AND " .
						   "game_id = $this->game_id LIMIT 1");
		if ($this->next_record()) {
			$this->alliance_name		= stripslashes($this->f("alliance_name"));
		} else {
			$this->alliance_name = "none";
		}


		$this->game_type = SmrSession::$game_type;
		//we can't use session for game speed because the play game screen needs the speed and has no game id
		$this->query("SELECT * FROM game WHERE game_id = $game_id LIMIT 1");
		$this->next_record();
		$this->game_speed = $this->f("game_speed");

	}

    function sector_change() {
      $this->sector_change = mt_rand();
    }
    
	function get_display_xp_lvl() {
		// determine display exp
		$this->query("SELECT experience FROM player_cache WHERE account_id = $this->account_id " .
						"AND game_id = $this->game_id LIMIT 1");
		if ($this->next_record()) {
			
			$this->display_experience = $this->f("experience");
			
		} else $this->display_experience = 0;

		// determine user display level
		$this->query("SELECT * FROM level WHERE requirement <= $this->display_experience " .
										 "ORDER BY requirement DESC LIMIT 1");
		if ($this->next_record()) {

			$this->display_level_id			= $this->f("level_id");
			$this->display_level_name		= $this->f("level_name");

		}
	}
	function get_relations() {
		$this->relations = array();
		$this->relations_global = array();
		$this->relations_global_rev = array();

		// initialize relations with 0
		for($i=0;$i<10;++$i){
			$this->relations[$i] = 0;
			$this->relations_global[$i] = 0;
			$this->relations_global_rev[$i] = 0;
		}
		
		$this->query("SELECT * FROM player_has_relation WHERE account_id = $this->account_id AND game_id = $this->game_id");
		while ($this->next_record()) {
			$this->relations[$this->f("race_id")] = $this->f("relation");
		}

		$this->query("SELECT * FROM race_has_relation WHERE (race_id_1 = $this->race_id OR race_id_2 = $this->race_id) AND game_id = $this->game_id");

		while ($this->next_record()) {
			if($this->f('race_id_1') == $this->race_id){
				$this->relations_global[$this->f("race_id_2")] = $this->f("relation");	
			}
			if($this->f('race_id_2') == $this->race_id){
				$this->relations_global_rev[$this->f("race_id_1")] = $this->f("relation");			
			}
		}

		// Alskants have the same relations with neutral ports that they do with Alskant
		if($this->race_id == 2) {
			$this->relations[1] = &$this->relations[2];
			$this->relations_global[1] = &$this->relations_global[2];
			$this->relations_global_rev[1] = &$this->relations_global_rev[2];
		}
		
	}
	
	function get_info($name) {
		$this->query("SELECT $name FROM player WHERE game_id = $this->game_id AND account_id = $this->account_id LIMIT 1");
		$this->next_record();
		$value = $this->f($name);
		$this->{$name} = $value;
		return $value;

	}

	function set_info($name, $new_info) {

		if ($name == 'relations') {

			foreach ($this->relations as $id => $relation)
			if ($id > 1)
				$this->query("REPLACE INTO player_has_relation (account_id, game_id, race_id, relation) VALUES($this->account_id, $this->game_id, $id, $relation)");

		} elseif ($name == 'sector' || $name == 'sector_id')
			$this->query("UPDATE player SET sector_id = $new_info WHERE game_id = $this->game_id AND account_id = $this->account_id LIMIT 1");
		elseif (is_numeric($new_info)) {

			$old_info = $this->get_info($name);
			$diff_info = $new_info - $old_info;
			$this->query("UPDATE player SET $name = $name + $diff_info WHERE game_id = $this->game_id AND account_id = $this->account_id LIMIT 1");

		} else
			$this->query("UPDATE player SET $name = '$new_info' WHERE game_id = $this->game_id AND account_id = $this->account_id LIMIT 1");

		$this->{$name} = $new_info;

	}

	function update_turns($ship_speed) {

		// is account validated?
		if ($this->validated == "FALSE") return;

		// update turns?
		$time_diff = time() - $this->last_turn_update;

		// how many turns would he get right now?
		$new_turns = floor($time_diff * $ship_speed * $this->game_speed / 3600);

		// do we have at least one turn to give?
		if ($new_turns > 0) {

			// recalc the time to avoid errors
			$this->last_turn_update += ceil($new_turns * 3600 / $ship_speed / $this->game_speed);

			// max turns are dependent on game speed (Unless you are thevian)	
			//if ($this->race_id == 7) {
				$max_turns = 400 * $this->game_speed;
			//}
			//else {
			//	$max_turns = 500 * $this->game_speed;
			//}
			// do we have more than (400 * $this->game_speed) turns?
			if ($this->turns + $new_turns > $max_turns) {

				// don't get more than $max_turns turns
				$new_turns = $max_turns - $this->turns;

				// but don't take turns away
				if ($new_turns < 0)
					$new_turns = 0;

			}

			// add the new turns to our current turns
			$this->turns += $new_turns;

		}

	}

	function is_fed_protected() {
//exit;
        global $session;
        
		// If there isn't a fed beacon we aren't protected
		$this->query("SELECT sector_id FROM location " .
					 "WHERE location_type_id=201 AND sector_id = $this->sector_id AND " .
						   "game_id = $this->game_id LIMIT 1");
		if (!$this->nf()) {
			return 0;
		}	
			
		// for rating
		$this->query("SELECT sum(shield_damage), sum(armor_damage) FROM ship_has_weapon NATURAL JOIN weapon_type " .
					 "WHERE account_id = $this->account_id AND " .
						   "game_id = $this->game_id");
		if ($this->next_record()) {

			$sum_shield_damage = $this->f("sum(shield_damage)");
			$sum_armor_damage = $this->f("sum(armor_damage)");

		}

		$this->query("SELECT amount FROM ship_has_hardware " .
					 "WHERE account_id = $this->account_id AND " .
						   "game_id = $this->game_id AND " .
						   "hardware_type_id = 4 LIMIT 1");
		if ($this->next_record())
			$combat_drones = $this->f("amount");
		$total_damage = $sum_shield_damage + $sum_armor_damage;
		$maxDronesPercent = (35 + $this->level_id * .6 + ($this->level_id - 1) * .4 + 15) * .01;
		$maxDrones = $maxDronesPercent * $combat_drones;
		$attack_rating = round((($total_damage + $maxDrones * 2) / 40));

        // If the account matches the session then we have the correct ship in globals
		if($this->account_id != SmrSession::$old_account_id) {
          $ship = new SMR_SHIP($this->account_id, $this->game_id);
        }
        else {
          $ship = $GLOBALS['ship'];
        }
        
		if ($ship->cargo[5] > 0 || $ship->cargo[9] > 0 || $ship->cargo[12] > 0)
			return 0;
		elseif ($attack_rating > 8)
			return 0;
		elseif ($attack_rating == 0)
			return 1;
		elseif ($this->alignment >= 0 && $this->alignment >= ($attack_rating - 3) * 150)
			return 1;
		elseif ($this->alignment < 0 && $this->alignment > ($attack_rating - 4) * 150)
			return 1;
		else
			return 0;
	}


	function update() {
		$query = "UPDATE player SET credits = $this->credits, " .
				"turns = $this->turns, " .
				"newbie_turns = $this->newbie_turns, " .
				"experience = $this->experience, " .
				"alignment = $this->alignment, " .
				"military_payment = $this->military_payment, " .
				"sector_id = $this->sector_id, " .
				"last_sector_id = $this->last_sector_id, " .
				"controlled = '$this->controlled', " .
				"bank = $this->bank, " .
				"kills = $this->kills, " .
				"last_turn_update = $this->last_turn_update, " .
				"last_ticker_update = $this->last_ticker_update, " .
				"last_active = $this->last_active, " . 
                "sector_change = $this->sector_change, " .
                "safe_exit= $this->safe_exit," .   
                "detected='$this->detected',";

		$query .= "deaths = $this->deaths, dead = '$this->dead', ";


		$query .= 	"land_on_planet = '$this->land_on_planet', " .
				"newbie_warning = '$this->newbie_warning', " .
				"last_news_update = $this->last_news_update, " .
				"alliance_id = $this->alliance_id " .
				"WHERE account_id = $this->account_id AND " .
				"game_id = $this->game_id";

		$this->query($query);
		if(isset($this->relations)) {
			foreach ($this->relations as $id => $relation)
				if ($id > 1)
					$this->query("REPLACE INTO player_has_relation (account_id, game_id, race_id, relation) VALUES($this->account_id, $this->game_id, $id, $relation)");
		}

	}

	function unread_msg() {

		$this->query("SELECT account_id FROM message WHERE account_id = $this->account_id AND " .
												 "game_id = $this->game_id AND " .
												 "msg_read = 'FALSE' LIMIT 1");
		return $this->nf();

	}

	function mark_dead() {

		$this->set_info('dead', "TRUE");
		$this->set_info('newbie_warning', "TRUE");

	}

	function died($news_message) {

		global $sector;
		$news_message = strip_tags($news_message, "<font><span>");
		//msg taken care of in trader_att_proc.php
		// forget plotted course
		$this->delete_plotted_course();

		// insert the news entry
		$this->query("INSERT INTO news " .
					 "(game_id, time, news_message) " .
					 "VALUES($this->game_id, " . time() . ", " . format_string($news_message, false) . ")");

		// the sector saw a battle now
		$this->query("UPDATE sector SET battles = battles + 1 " .
					 "WHERE game_id = $this->game_id AND sector_id = $this->sector_id LIMIT 1");

		// if we are in an alliance we increase their deaths
		if ($this->alliance_id != 0)
			$this->query("UPDATE alliance SET alliance_deaths = alliance_deaths + 1 " .
						 "WHERE game_id = $this->game_id AND alliance_id = $this->alliance_id LIMIT 1");

		// record death stat
		$this->update_stat("deaths", 1);

		// reset the turns used since last death stat
		$this->query("UPDATE account_has_stats SET turns_used = 0 WHERE account_id = $this->account_id LIMIT 1");

		// 1/4 of ship value -> insurance
		$this->query("SELECT cost, speed FROM player, ship_type " .
					 "WHERE player.ship_type_id = ship_type.ship_type_id AND " .
					 "account_id = $this->account_id AND " .
						   "game_id = $this->game_id LIMIT 1");
		if ($this->next_record()) {

			$this->credits = round($this->f("cost") / 4);
			$old_speed = $this->f("speed");

		}
		if ($this->credits < 5000)
			$this->credits = 5000;
		$this->set_info('credits', $this->credits);

		// speed for pod
		$new_speed = 7;

		// max turns
		$max_turns = 400 * $this->game_speed;

		// adapt turns
		$this->turns = round($this->turns / $old_speed * $new_speed);

		if ($this->turns > $max_turns)
			$this->turns = $max_turns;

		$this->sector_id = $this->get_home();
		$this->deaths += 1;
		$this->set_info('turns', $this->turns);
		$this->set_info('land_on_planet', "FALSE");
		$this->set_info('sector', $this->sector_id);
		$this->set_info('newbie_turns', 100);
		$this->set_info('deaths', $this->deaths);

	}

	function died_by_planet($owner_id) {

		// send the a msg to user
		global  $player, $sector;
		
		$planet = new SMR_PLANET($player->sector_id, $player->game_id);
		
		$sector->died_here();

		$owner = new SMR_PLAYER($owner_id, $this->game_id);
		$this->send_message($owner->account_id, MSG_PLAYER, format_string("Your planet <span style=\"color:red;\">DESTROYED</span> $this->player_name in sector $planet->sector_id", false));
		$owner->send_message($this->account_id, MSG_PLAYER, format_string("You were <span style=\"color:red;\">DESTROYED</span> by Planetary defenses", false));
		
		$news_message = $this->get_colored_name();
		$db2 = new Smr12MySqlDatabase();
		$db = new Smr12MySqlDatabase();
		$db2->query("SELECT * FROM ship_has_name WHERE game_id = $this->game_id AND " .
					"account_id = $this->account_id");
		if ($db2->next_record()) {
			
			//they have a name so we print it
			$db->query("SELECT '" . htmlentities($db2->f("ship_name")) . "' LIKE '&lt;img%'");
			$named_ship = stripslashes($db2->f("ship_name"));
			$named_ship = strip_tags($db2->f("ship_name"), "<font><span>");
			if ($db->next_record() && $db->f(0) != 0) {
				//nothing
				//print("SELECT '" . htmlentities($db2->f("ship_name")) . "' LIKE '&lt;img%'");
			} else {
				//print("SELECT '" . htmlentities($db2->f("ship_name")) . "' LIKE '&lt;img%'");
				$news_message .= " flying <font color=\"yellow\">$named_ship</font>";
			}
			
		}
		$news_message .= " was killed while " .
						"invading a planet in sector&nbsp#$planet->sector_id";

		$this->died($news_message);
		$percentage = 50 - $planet->level() / 2;
		if ($percentage > 0 )
			$this->experience -= $this->experience * $percentage / 100;
		$this->set_info('experience', $this->experience);
		$db->query("SELECT * FROM alliance_vs_alliance WHERE alliance_id_1 = -2 AND " . 
						"alliance_id_2 = $this->alliance_id AND game_id = $this->game_id");
		if ($db->next_record()) $db->query("UPDATE alliance_vs_alliance SET kills = kills + 1 " .
											"WHERE alliance_id_1 = -2 AND alliance_id_2 = $this->alliance_id AND " .
											"game_id = $this->game_id");
		else $db->query("REPLACE INTO alliance_vs_alliance (game_id, alliance_id_1, alliance_id_2, kills) " .
						"VALUES ($this->game_id, -2, $this->alliance_id, 1)");

	}

	function died_by_port() {

		global  $sector;
		$sector->died_here();

		// send the a msg to user
		$this->send_message($this->account_id, MSG_PLAYER, format_string("You were <span style=\"color:red;\">DESTROYED</span> by Port defenses", false));
		usleep(1000000);
		$news_message = $this->get_colored_name();
		$db2 = new Smr12MySqlDatabase();
		$db = new Smr12MySqlDatabase();
		$db2->query("SELECT * FROM ship_has_name WHERE game_id = $this->game_id AND " .
					"account_id = $this->account_id");
		if ($db2->next_record()) {
			
			//they have a name so we print it
			$db->query("SELECT '" . htmlentities($db2->f("ship_name")) . "' LIKE '&lt;img%'");
			$named_ship = stripslashes($db2->f("ship_name"));
			$named_ship = strip_tags($db2->f("ship_name"), "<font><span>");
			if ($db->next_record() && $db->f(0) != 0) {
				//nothing
			} else
				$news_message .= " flying <font color=\"yellow\">$named_ship</font>";
			
		}
		$news_message .= " was killed while " .
						"invading a port in sector&nbsp#$this->sector_id";

		$this->died($news_message);
		$percentage = 25;
		if ($percentage > 0 )
			$this->experience -= $this->experience * $percentage / 100;
		$this->set_info('experience', $this->experience);
		$db->query("SELECT * FROM alliance_vs_alliance WHERE alliance_id_1 = -3 AND " . 
						"alliance_id_2 = $this->alliance_id AND game_id = $this->game_id");
		if ($db->next_record()) $db->query("UPDATE alliance_vs_alliance SET kills = kills + 1 " .
											"WHERE alliance_id_1 = -3 AND alliance_id_2 = $this->alliance_id AND " .
											"game_id = $this->game_id");
		else $db->query("REPLACE INTO alliance_vs_alliance (game_id, alliance_id_1, alliance_id_2, kills) " .
						"VALUES ($this->game_id, -3, $this->alliance_id, 1)");

	}

	function died_by_forces($owner_id) {

		global  $sector;
		$sector->died_here();

		// send a message to the person who died
		$owner = new SMR_PLAYER($owner_id, $this->game_id);
		$this->send_message($owner->account_id, MSG_PLAYER, format_string("Your forces <span style=\"color:red;\">DESTROYED</span> $this->player_name in sector $this->sector_id", false));
		$owner->send_message($this->account_id, MSG_PLAYER, format_string("You were <span style=\"color:red;\">DESTROYED</span> by forces in sector $this->sector_id", false));

		$news_message = $this->get_colored_name();
		$db2 = new Smr12MySqlDatabase();
		$db = new Smr12MySqlDatabase();
		$db2->query("SELECT * FROM ship_has_name WHERE game_id = $this->game_id AND " .
					"account_id = $this->account_id");
		if ($db2->next_record()) {
			
			//they have a name so we print it
			$db->query("SELECT '" . htmlentities($db2->f("ship_name")) . "' LIKE '&lt;img%'");
			$named_ship = stripslashes($db2->f("ship_name"));
			$named_ship = strip_tags($db2->f("ship_name"), "<font><span>");
			if ($db->next_record() && $db->f(0) != 0) {
				//nothing
			} else
				$news_message .= " flying <font color=\"yellow\">$named_ship</font>";
			
		}
		$news_message .= " was destroyed " .
						"by forces in sector&nbsp#$this->sector_id";

		$this->died($news_message);
		$percentage = 33;
		if ($percentage > 0 )
			$this->experience -= $this->experience * $percentage / 100;
		$this->set_info('experience', $this->experience);
		$db->query("SELECT * FROM alliance_vs_alliance WHERE alliance_id_1 = -1 AND " . 
						"alliance_id_2 = $this->alliance_id AND game_id = $this->game_id");
		if ($db->next_record()) $db->query("UPDATE alliance_vs_alliance SET kills = kills + 1 " .
											"WHERE alliance_id_1 = -1 AND alliance_id_2 = $this->alliance_id AND " .
											"game_id = $this->game_id");
		else $db->query("REPLACE INTO alliance_vs_alliance (game_id, alliance_id_1, alliance_id_2, kills) " .
						"VALUES ($this->game_id, -1, $this->alliance_id, 1)");
		
	}

	function send_message($receiver_id, $message_type_id, $message) {
		
		// Don't send messages to players ignoring us
		$this->query('SELECT account_id FROM message_blacklist WHERE account_id=' . $receiver_id . ' AND blacklisted_id=' . $this->account_id . ' AND game_id = ' . $this->game_id . ' LIMIT 1');
		if($this->next_record()) {
			return;
		}		
		
		$message = word_filter($message);
		
		if ($message_type_id == MSG_GLOBAL) $expire = 3600;
		elseif ($message_type_id == MSG_PLAYER) $expire = 3600 * 24 * 7;
		elseif ($message_type_id == MSG_PLANET) $expire = 3600 * 24 * 3;
		elseif ($message_type_id == MSG_SCOUT) $expire = 3600 * 24 * 3;
		elseif ($message_type_id == MSG_POLITICAL) $expire = 3600 * 24 * 2;
		elseif ($message_type_id == MSG_ALLIANCE) $expire = 3600 * 24 * 2;
		elseif ($message_type_id == MSG_ADMIN) $expire = 3600 * 24 * 7;
		else $expire = 3600 * 24 * 2;
		$expire += time();
		// send him the message
		$this->query('
			INSERT INTO	message
			(account_id,game_id,message_type_id,message_text,
			sender_id,send_time,expire_time) VALUES(' . 
			$receiver_id . ',' .
			$this->game_id . ',' .
			$message_type_id . ',' .
			$message . ',' .
			$this->account_id . ',' .
			time() . ',' .
			$expire . ')'
		);

		// give him the message icon
		$this->query("REPLACE INTO player_has_unread_messages (game_id, account_id, message_type_id) VALUES " .
						"($this->game_id, $receiver_id, $message_type_id)");

	}

	function take_turns($number) {

		// take turns
		$this->turns -= $number;
		if($this->turns < 0) $this->turns=0;
		// take newbie turns (if we have)
		if ($this->newbie_turns > 0) {

			$this->newbie_turns -= $number;
			if ($this->newbie_turns < 0)
				$this->newbie_turns = 0;

		}

		$this->last_active = time();
		$this->update_stat("turns_used", $number);

	}

	function get_home() {

		// get his home sector
		$hq_id = GOVERNMENT + $this->race_id;
		$this->query("SELECT location.sector_id FROM location NATURAL JOIN sector " .
					 "WHERE location.game_id = $this->game_id AND " .
						   "location_type_id = $hq_id LIMIT 1");
		if ($this->next_record())
			return $this->f("sector_id");
		else
			return 1;

	}

	function get_colored_name() {

		return get_colored_text($this->alignment, "$this->player_name&nbsp;($this->player_id)");

	}

	function get_colored_race($race_id) {

		$this->query("SELECT race_name FROM race " .
					 "WHERE race_id = $race_id LIMIT 1");
		if ($this->next_record())
			$race_name = $this->f("race_name");

		return get_colored_text($this->relations_global[$race_id], "$race_name");

	}

	function delete_plotted_course() {

		$this->query("DELETE FROM player_plotted_course " .
					 "WHERE account_id = $this->account_id AND " .
						   "game_id = $this->game_id LIMIT 1");

	}

	function update_stat($stat_type, $add_value) {

		$this->query("UPDATE account_has_stats SET $stat_type = $stat_type + $add_value " .
					"WHERE account_id = $this->account_id LIMIT 1");
		//curr game stats
		$this->query("UPDATE player_has_stats SET $stat_type = $stat_type + $add_value " .
					 "WHERE account_id = $this->account_id AND game_id = $this->game_id LIMIT 1");

	}

	function add_message($message_type_id) {

		$this->query("REPLACE INTO player_has_unread_messages (game_id, account_id, message_type_id) VALUES " .
						"($this->game_id, $this->account_id, $message_type_id)");

	}

	function remove_message($message_type_id) {

		$this->query("DELETE FROM player_has_unread_messages WHERE game_id = $this->game_id AND " .
					"account_id = $this->account_id AND " .
					"message_type_id = $message_type_id");

	}

}

?>
