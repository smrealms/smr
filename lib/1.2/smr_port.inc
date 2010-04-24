<?php

// include file with database class
//require_once(LIB . "SmrMySqlDatabase.inc");
require_once(get_file_loc('SmrMySqlDatabase.class.inc'));

class SMR_PORT extends SmrMySqlDatabase {

    var $sector_id;
    var $game_id;
    var $level;
    var $race_id;
    var $race_name;
    var $amount;
    var $base_price;
    var $transaction;
    var $max_amount;
    var $gained_experience;
    var $last_updated;
    var $shields;
    var $armor;
    var $drones;
    var $credits;
    var $attack_started;
    var $refresh_defense;
    var $upgrade;

    function SMR_PORT($sector_id, $game_id) {

        $this->sector_id = $sector_id;
        $this->game_id = $game_id;

        $this->query("SELECT * FROM port NATURAL JOIN race " .
                     "WHERE game_id = $this->game_id AND " .
                            "sector_id = $this->sector_id");
        if ($this->next_record()) {

            $this->level				= $this->f("level");
            $this->race_id				= $this->f("race_id");
            $this->race_name			= $this->f("race_name");
            $this->gained_experience	= $this->f("gained_experience");
            $this->last_updated			= $this->f("last_updated");
            $this->shields				= $this->f("shields");
            $this->armor				= $this->f("armor");
            $this->drones				= $this->f("drones");
            $this->credits				= $this->f("credits");
            $this->attack_started		= $this->f("attack_started");
            $this->refresh_defense		= $this->f("refresh_defense");
            $this->upgrade				= $this->f("upgrade");

        }

        $this->amount = array();
        $this->base_price = array();
        $this->transaction = array();
        $this->max_amount = array();

        $this->query("SELECT * FROM port_has_goods NATURAL JOIN good " .
                     "WHERE sector_id = $this->sector_id AND game_id = $game_id");
        while ($this->next_record()) {

            $good_id                        = $this->f("good_id");
            $this->amount[$good_id]            = $this->f("amount");
            $this->base_price[$good_id]        = $this->f("base_price");
            $this->transaction[$good_id]    = $this->f("transaction");
            $this->max_amount[$good_id]        = $this->f("max_amount");

        }
		if(!isset($GLOBALS['player'])) {
			$this->query("SELECT game_speed FROM game WHERE game_id = $this->game_id LIMIT 1");
			if ($this->next_record())
				$this->refill($this->f("game_speed"));
		}
		else {
			$this->refill($GLOBALS['player']->game_speed);
		}

    }

    function update() {

        $this->query("UPDATE `port` SET `gained_experience` = $this->gained_experience, " .
                                     "`last_updated` = $this->last_updated, " .
                                     "`shields` = $this->shields, " .
                                     "`armor` = $this->armor, " .
                                     "`drones` = $this->drones, " .
                                     "`level` = $this->level, " .
                                     "`credits` = $this->credits, " .
                                     "`upgrade` = $this->upgrade, " .
                                     "`refresh_defense` = $this->refresh_defense, " .
                                     "`attack_started` = $this->attack_started " .
                                 "WHERE `game_id` = $this->game_id AND " .
                                        "`sector_id` = $this->sector_id LIMIT 1");

        foreach ($this->amount as $id => $amount)
            $this->query("UPDATE port_has_goods SET amount = $amount " .
                         "WHERE game_id = $this->game_id AND " .
                              "sector_id = $this->sector_id AND " .
                              "good_id = $id LIMIT 1");
    }

    function refill($game_speed) {

        // units per hour
        $refill = 250;

        // update turns?
        $time_diff = time() - $this->last_updated;

		// how many new items would it get right now?
		$new_amount = floor($time_diff * $refill * $game_speed / 3600);

		// do we have at least one item to give?
		if ($new_amount > 0) {

			// recalc the time to avoid errors
			$this->last_updated += ceil($new_amount * 3600 / $refill / $game_speed);

            // add the amount to each good in port
            foreach($this->amount as $id => $amount) {

                // add the new amount to existing amount
                $this->amount[$id] += $new_amount;

                if ($this->amount[$id] > $this->max_amount[$id])
                	$this->amount[$id] = $this->max_amount[$id];

            }

            // save amount to db
            $this->update();

        }

    }

    function upgrade() {

		// If they aren't level 9 we add a good and a level
		if($this->level != 9) {
        	$this->level++;
			$this->add_good();
		}

		// Rich ports get better defences
		$rich_mod = floor(($this->credits) * 1e-7);
		if($rich_mod < 0) $rich_mod = 0;

		$this->shields = ($this->level * 1000 + 1000) + ($rich_mod * 500);
		$this->armor = ($this->level * 1000 + 1000) + ($rich_mod * 500);
		$this->drones = ($this->level * 100 + 100) + ($rich_mod * 50);

		$this->upgrade = 0;
        $this->update();

    }

    function add_good() {

        mt_srand((double)microtime()*1000000);

        if ($this->level == 1 || $this->level == 2)
            $good_class = 1;
        if ($this->level == 3 || $this->level == 4 || $this->level == 5 || $this->level == 6)
            $good_class = 2;
        if ($this->level == 7 || $this->level == 8 || $this->level == 9)
            $good_class = 3;

        $this->query("SELECT * FROM good WHERE good_class = $good_class " .
                                        "ORDER BY rand()");
		$finished = false;
        while(!$finished && $this->next_record()) {

            $good_id = $this->f("good_id");

            $db = new SmrMySqlDatabase();
            $db->query("SELECT * FROM port_has_goods WHERE game_id = $this->game_id AND " .
                                                          "sector_id = $this->sector_id AND " .
                                                          "good_id = $good_id");
            // only if this good doesn't exist yet
            if ($db->nf() == 0) {

                // get transaction
                if (mt_rand(0, 1) == 1)
                    $transaction = "Buy";
                else
                    $transaction = "Sell";

                // don't get more than 8 goods on one side
                $db->query("SELECT * FROM port_has_goods WHERE game_id = $this->game_id AND " .
                                                              "sector_id = $this->sector_id AND " .
                                                              "transaction = '$transaction'");
                // negate transaction if it's already too much
                if ($db->nf() > 7)
                    if ($transaction == "Buy")
                        $transaction = "Sell";
                    else
                        $transaction = "Buy";

                $db->query("SELECT * FROM good WHERE good_id = $good_id");
                if ($db->next_record())
                    $max_amount = $db->f("max_amount");

                $db->query("INSERT INTO port_has_goods (game_id, sector_id, good_id, transaction, amount) " .
                                                "VALUES ($this->game_id, $this->sector_id, $good_id, '$transaction', $max_amount)");
                $finished = true;

            }

        }

    }

    function downgrade() {
        //downgrades are now done in port_attack_processing_new.php since it is the only call of the function
    }

}

?>