<?php
require_once(get_file_loc('smr_sector.inc'));
		$sector = new SMR_SECTOR($player->sector_id, SmrSession::$game_id, SmrSession::$old_account_id);

require_once(get_file_loc("smr_battle.inc"));

if (isset($_COOKIE["Legit"])) {
	
	setcookie("Legit",2,time()-3600);
	$db->query("SELECT * FROM macro_check WHERE account_id = $player->account_id");
	if($db->next_record())
		$db->query("UPDATE macro_check SET good = good + 1 WHERE account_id = $player->account_id");
	else
		$db->query("REPLACE INTO macro_check (account_id, good) VALUES ($player->account_id, 1)");
	
} elseif (!$var["legit"]) {
	
	$db->query("SELECT * FROM macro_check WHERE account_id = $player->account_id");
	if($db->next_record())
		$db->query("UPDATE macro_check SET bad = bad + 1 WHERE account_id = $player->account_id");
	else
		$db->query("REPLACE INTO macro_check (account_id, bad) VALUES ($player->account_id, 1)");
		
}

//this might help
include(get_file_loc("trader_attack.inc"));
$db2 = new SmrMySqlDatabase();
// initialize random generator.
mt_srand((double)microtime()*1000000);

// creates a new player object for attacker and defender
$attacker_id = SmrSession::$old_account_id;
$defender_id = $var["target"];

$attacker_team = new SMR_BATTLE($attacker_id, SmrSession::$game_id);
$defender_team = new SMR_BATTLE($defender_id, SmrSession::$game_id);
$sector_id = $player->sector_id;
// is the defender on the planet?
// or did he left the sector?
// or is he dead?
$db->query("SELECT * FROM player " .
		   "WHERE account_id = $defender_id AND " .
				 "dead = 'TRUE' AND " .
				 "game_id = ".SmrSession::$game_id);
if ($db->nf() == 1)
	create_error("Your target is already dead!");

$db->query("SELECT * FROM player " .
		   "WHERE sector_id = $player->sector_id AND " .
				 "account_id = $defender_id AND " .
				 "land_on_planet = 'FALSE' AND " .
				 "game_id = ".SmrSession::$game_id);
if ($db->nf() == 0)
	create_error("Your target has left the sector!");

// check if he got enough turns
if ($player->get_info('turns') < 3)
	create_error("You do not have enough turns to attack this trader!");

// take the turns
$player->take_turns(3);
$player->update();

$defender = new SMR_PLAYER($defender_id, SmrSession::$game_id);

// log action
$account->log(8, "Attacks $defender->player_name", $player->sector_id);

$container = array();
$container["url"] = "skeleton.php";
$container["body"] = "trader_attack.php";

// attacker shoots
$container["attackerguy"] = shoot_trader($attacker_team, $defender_team);

// defender shoots
$container["defenderguy"] = shoot_trader($defender_team, $attacker_team);


// ***********************************
// *
// * U p d a t i n g	D a t a b a s e
// *
// ***********************************

// debug the death
$debug = false;

// assume yes for now
$container["continue"] = "Yes";

// new db object to avoid interfearance with other db-objects
$dead_ppl = new SmrMySqlDatabase();

// we need to enter the loop at least once
$result = true;

// with a while loop we have to pick up only one result row from the db query
// do all our work (so over threads have the ability to pick up other rows)
// then lock table again to pic up another row
while ($result) {

	// lock kills table for the whole process
	$dead_ppl->lock("kills");

	// pick one random row we are going to process
	$dead_ppl->query("SELECT *
					  FROM kills
					  WHERE game_id = $player->game_id AND
					  processed = 'FALSE'
					  ORDER BY rand()
					  LIMIT 1
					 ");

	// did we get one result?
	if ($dead_ppl->next_record()) {

		// get their id's
		$killed_id = $dead_ppl->f("dead_id");
		$killer_id = $dead_ppl->f("killer_id");
		$curr_sector = $dead_ppl->f("sector_id");
		$dead_exp = $dead_ppl->f("dead_exp");
		$kill_exp = $dead_ppl->f("kill_exp");

		// we have to set the 'process' column to true here
		// BEFORE we give access free to that table.
		// otherwise another thread could pick this row up
		// but we cannot delte the entry before we sent that poor guy back to his hq
		$db->query("UPDATE kills SET processed = 'TRUE' WHERE game_id = $player->game_id AND dead_id = $killed_id AND killer_id = $killer_id");

		// give table free
		$dead_ppl->unlock();

	if ($debug) print("<p>report any errors on that page to spock</p>");

		// create player object
		// JUST FOR READING!!!
		// DO NOT CHANGE SOMETHING IN THERE!
		$killed = new SMR_PLAYER($killed_id, SmrSession::$game_id);
		$killer = new SMR_PLAYER($killer_id, SmrSession::$game_id);

		// is one of the dead guys the original attacker or defender?
		if ($killed_id == $defender_id || $killed_id == $attacker_id)
			$container["continue"] = "No";

		// save some time here
		$killer_name = get_colored_text($killer->alignment, $killer->player_name);
		$killed_name = get_colored_text($killed->alignment, $killed->player_name);

	if ($debug) print("newbie turns<br>");
		$db->query("UPDATE player SET newbie_turns = 100 WHERE account_id = $killed_id AND game_id = $player->game_id");

	if ($debug) print("insurance<br>");
		// 1/4 of ship value -> insurance
		$db->query("SELECT cost, speed FROM player, ship_type " .
				   "WHERE player.ship_type_id = ship_type.ship_type_id AND " .
						 "account_id = $killed_id AND " .
						 "game_id = $player->game_id");
		if ($db->next_record()) {

			$credits = round($db->f("cost") / 4);

			// never less than 5k credits
			if ($credits < 5000)
				$credits = 5000;

			$speed = $db->f("speed");

		} else {

			// if db fails we initialize vars
			$credits = 5000;
			$speed = 7;

		}

		$db->query("UPDATE player SET credits = $credits WHERE account_id = $killed_id AND game_id = $player->game_id");

	if ($debug) print("turns<br>");
		// speed for pod
		$pod_speed = 7;

		// adapt turns
		$turns = round($killed->turns * $pod_speed / $speed);

		// max turns
		$max_turns = 400 * $killed->game_speed;

		if ($turns > $max_turns)
			$turns = $max_turns;

		// update turns
		$db->query("UPDATE player SET turns = $turns WHERE account_id = $killed_id AND game_id = $player->game_id");

	if ($debug) print("attacker xp<br>");
		/*
		$e = experience gained
		$LV = level of the victor
		$LD = level of the defeated
		$P = total experience of the defeated
		$k1 = variable #1 1/25
		$k2 = variable #2 1/40
		*/
		$db->query("SELECT * FROM level WHERE requirement < $kill_exp ORDER BY level_id DESC LIMIT 1");
		$db->next_record();
		$lv = $db->f("level_id");
		$db->query("SELECT * FROM level WHERE requirement < $dead_exp ORDER BY level_id DESC LIMIT 1");
		$db->next_record();
		$ld = $db->f("level_id");
		$p = $dead_exp;
		$k1 = 1 / 25;
		$k2 = 1 / 40;
		if ($ld >= $lv)
			$e = ((($ld - $lv) / $ld) + 1) * ($k1) * ($p) + ($k2) * ($p);
		else
			$e = ((($ld - $lv) / $lv) + 1) * ($k1) * ($p) + ($k2) * ($p);
		
		
		//$gained_exp = $dead_exp / 30;
		$gained_exp = $e;
		$db->query("UPDATE player SET experience = experience + $gained_exp WHERE account_id = $killer_id AND game_id = $player->game_id");

	if ($debug) print("defender xp<br>");
		$percentage = 20 + (($killed->level_id - $killer->level_id) / 2);
		if ($percentage > 0) {

			$killed_exp = round($killed->experience * $percentage / 100);
			$db->query("UPDATE player SET experience = experience - $killed_exp WHERE account_id = $killed_id AND game_id = $player->game_id");

		} else
			$killed_exp = 0;

	if ($debug) print("hof stats<br>");
		// update his stats
		$killer->update_stat("kills", 1);
		$killer->update_stat("kill_exp", $gained_exp);
		$killer->update_stat("traders_killed_exp", $killed->experience);
		
	if ($debug) print("alliance updating stats<br>");
		//update alliance vs alliance
		$db->query("SELECT * FROM alliance_vs_alliance WHERE alliance_id_1 = $killer->alliance_id AND " .
					"alliance_id_2 = $killed->alliance_id AND game_id = $killer->game_id");
		if ($db->next_record()) $db->query("UPDATE alliance_vs_alliance SET kills = kills + 1 " .
							"WHERE game_id = $killer->game_id AND " .
							"alliance_id_1 = $killer->alliance_id AND " .
							"alliance_id_2 = $killed->alliance_id");
		else $db->query("REPLACE INTO alliance_vs_alliance (game_id, alliance_id_1, alliance_id_2, kills) " .
					"VALUES ($killer->game_id, $killer->alliance_id, $killed->alliance_id, 1)");

	if ($debug) print("alliance kills<br>");
		// when he's in an alliance increase their kills
		if (!empty($killer->alliance_id))
			$db->query("UPDATE alliance SET alliance_kills = alliance_kills + 1 WHERE game_id = $player->game_id AND alliance_id = $killer->alliance_id");

	if ($debug) print("giving attacker money<br>");
		// give him our money
		$db->query("UPDATE player SET credits = credits + $killed->credits WHERE account_id = $killer_id AND game_id = $player->game_id");
		$killer->update_stat("money_gained",$killed->credits);

	if ($debug) print("credit the kill to attacker<br>");
		// add a kill
		$db->query("UPDATE player SET kills = kills + 1 WHERE account_id = $killer_id AND game_id = $player->game_id");

	if ($debug) print("send message to attacker<br>");
		// send dead msg
		$killed->send_message($killer->account_id, MSG_PLAYER,
							  format_string("You <span style=\"color:red;\">DESTROYED</span> $killed_name in Sector&nbsp<span style=\"color:blue;\">#$curr_sector</span>", false));

	if ($debug) print("send message to defender<br>");
		//send them a nice message
		$killer->send_message($killed_id, MSG_PLAYER,
							  format_string("You were <span style=\"color:red;\">DESTROYED</span> by $killer_name in Sector&nbsp<span style=\"color:blue;\">#$curr_sector</span>", false));

	if ($debug) print("change alignment<br>");
		// now we change align
		// @ war? bring it up @ peace? go down @ neutral? check aligns
		$killer->get_relations();
		if ($killer->relations_global[$killed->race_id] <= -300)
			$modifier = $killer->relations_global[$killed->race_id] / -25;
		elseif ($killer->relations_global[$killed->race_id] >= 300)
			$modifier = $killer->relations_global[$killed->race_id] / -25;
		else
			$modifier = $killed->alignment / -10;
		$db->query("UPDATE player SET alignment = alignment + $modifier WHERE account_id = $killer_id AND game_id = $player->game_id");

	if ($debug) print("military payment?<br>");
		// do we get military pay?
		// align * 50 * sqrt of sqrt / 1.5 of exp * .1
		if ($killer->relations_global[$killed->race_id] <= -300)
			$military_pay = $killer->relations_global[$killed->race_id] * 50 * (sqrt(sqrt($killed_exp / 1.5))) * -1;
		else
			$military_pay = 0;
		$db->query("UPDATE player SET military_payment = military_payment + $military_pay WHERE account_id = $killer_id AND game_id = $player->game_id");

	if ($debug) print("bounty?<br>");
		$db2 = new SmrMySqlDatabase();

		// can we claim a bounty?
		$db->query("SELECT * FROM bounty WHERE game_id = $player->game_id AND account_id = $killed_id AND claimer_id = 0");
		while ($db->next_record()) {

			//we have a winner
			$bounty_id = $db->f("bounty_id");

			$db2->query("UPDATE bounty SET claimer_id = $killer_id WHERE game_id = $player->game_id AND account_id = $killed_id AND bounty_id = $bounty_id");

		}

		// are we gonna auto place a bounty?!
		// check if align diff is enough
		if (abs($killer->alignment - $killed->alignment) >= 200) {

			// we have a potential auto bounty
			$difference = abs($killer->alignment - $killed->alignment);
			$amount = pow($difference, 2.56);
			if ($killed->alignment >= 100)
				$place = 'HQ';
			elseif ($killed->alignment <= -100)
				$place = 'UG';

			if (isset($place) && $amount >= 1) {

				$db->query("SELECT * FROM bounty " .
						   "WHERE game_id = $killer->game_id AND " .
								 "account_id = $killer->account_id AND " .
								 "claimer_id = 0 AND " .
								 "type = '$place'");
				if ($db->next_record()) {

					//include interest
					$bounty_id = $db->f("bounty_id");
					$time = time();
					$days = ($time - $db->f("time")) / 60 / 60 / 24;
					$curr_amount = round($db->f("amount")); // * pow(1.05, $days));
					$new_amount = $curr_amount + $amount;
					$db->query("UPDATE bounty SET amount = $new_amount, time = $time WHERE game_id = $player->game_id AND bounty_id = $bounty_id");

				} else {

					//$db->query("SELECT * FROM bounty WHERE game_id = $killed->game_id ORDER BY bounty_id DESC LIMIT 1");
					//if ($db->next_record())
					//	$bounty_id = $db->f("bounty_id") + 1;
					//else
					//	$bounty_id = 1;
					$time = time();
					if ($amount > 0)
						$db->query("INSERT INTO bounty (account_id, game_id, bounty_id, type, amount, claimer_id, time) " .
							   "VALUES ($killer_id, $player->game_id, NULL, '$place', $amount, 0, $time)");

				}

			}

		}

	if ($debug) print("get rid of plotted course<br>");
		// forget plotted course
		$db->query("DELETE FROM player_plotted_course WHERE account_id = $killed_id AND game_id = $player->game_id");

	if ($debug) print("news entry<br>");
		$killed->get_relations();
		// create news msg
		if ($killed->relations_global[$killer->race_id] <= -300 || $killer->relations_global[$killed->race_id] <= -300) {
			
			$news_message = "<span style=\"color:red;\">News from the War Front</span><br /> " .
							"$killed_name ($killed->race_name)";
			$db2->query("SELECT * FROM ship_has_name WHERE game_id = $player->game_id AND " .
					"account_id = $killed->account_id");
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
			$news_message .= " was killed by " .
							"$killer_name ($killer->race_name)";
							
			$db2->query("SELECT * FROM ship_has_name WHERE game_id = $player->game_id AND " .
					"account_id = $killer->account_id");
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
			$news_message .= " in Sector&nbsp#$curr_sector";
							
		} else {
			
			$news_message = "$killed_name";
			$db2->query("SELECT * FROM ship_has_name WHERE game_id = $player->game_id AND " .
					"account_id = $killed->account_id");
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
			$news_message .= " was killed by $killer_name";
			$db2->query("SELECT * FROM ship_has_name WHERE game_id = $player->game_id AND " .
					"account_id = $killer->account_id");
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
			$news_message .= " in Sector&nbsp#$curr_sector";
			
		}
		// insert the news entry
		$db->query("INSERT INTO news " .
				   "(game_id, time, news_message) " .
				   "VALUES($player->game_id, " . time() . ", " . format_string($news_message, false) . ")");

	if ($debug) print("increase sector battles<br>");
		// the sector saw a battle now
		$db->query("UPDATE sector SET battles = battles + 1 WHERE game_id = $player->game_id AND sector_id = $curr_sector");

	if ($debug) print("update alliance deaths<br>");
		// if we are in an alliance we increase their deaths
		if ($killed->alliance_id != 0)
			$db->query("UPDATE alliance SET alliance_deaths = alliance_deaths + 1 WHERE game_id = $player->game_id AND alliance_id = $killed->alliance_id");

	if ($debug) print("credit hof death<br>");
		// record death stat
		$killed->update_stat("deaths", 1);

	if ($debug) print("reset hof (turns used)<br>");
		// reset the turns used since last death stat
		$db->query("UPDATE account_has_stats SET turns_used = 0 WHERE account_id = $killed_id");
		$db->query("UPDATE player_has_stats SET turns_used = 0 WHERE account_id = $killed_id");

	if ($debug) print("back to hq<br>");
		// send him to his hq
		//send out scout msgs
		$sector->leaving_sector();
		$db->query("UPDATE player SET sector_id = " . $killed->get_home() . " WHERE account_id = $killed_id AND game_id = $player->game_id");

	if ($debug) print("increase player deaths<br>");
		// register deaths
		$db->query("UPDATE player SET deaths = deaths + 1 WHERE account_id = $killed_id AND game_id = $player->game_id");
		
	if ($debug) print("Update Ship Cost HoF<br>");
		$db->query("SELECT * FROM ship_type WHERE ship_type_id = $killed->ship_type_id");
		$db->next_record();
		$killer->update_stat("killed_ships",$db->f("cost"));
		$killed->update_stat("died_ships",$db->f("cost"));

	if ($debug) print("delete all weapons, hardware, illusion and cloak<br>");
		// get him a pod
		$db->query("DELETE FROM ship_has_weapon WHERE account_id = $killed_id AND game_id = $player->game_id");
		$db->query("DELETE FROM ship_has_cargo WHERE account_id = $killed_id AND game_id = $player->game_id");
		$db->query("DELETE FROM ship_has_hardware WHERE account_id = $killed_id AND game_id = $player->game_id");
		$db->query("DELETE FROM ship_has_illusion WHERE account_id = $killed_id AND game_id = $player->game_id");
		$db->query("DELETE FROM ship_is_cloaked WHERE account_id = $killed_id AND game_id = $player->game_id");

	if ($debug) print("give shields, armor and cargo<br>");
		$db->query("INSERT INTO ship_has_hardware (account_id, game_id, hardware_type_id, amount, old_amount) " .
				   "VALUES($killed_id, $player->game_id, HARDWARE_SHIELDS, 50, 50)");
		$db->query("INSERT INTO ship_has_hardware (account_id, game_id, hardware_type_id, amount, old_amount) " .
				   "VALUES($killed_id, $player->game_id, HARDWARE_ARMOR, 50, 50)");
		$db->query("INSERT INTO ship_has_hardware (account_id, game_id, hardware_type_id, amount, old_amount) " .
				   "VALUES($killed_id, $player->game_id, HARDWARE_CARGO, 5, 5)");

	if ($debug) print("make it a pod<br>");
		// actual pod
		$db->query("UPDATE player SET ship_type_id = 69 WHERE account_id = $killed_id AND game_id = $player->game_id");

		// did we attack and die?
		// we don't get a pod screen then
		if ($killed_id == $attacker_id)
			$db->query("UPDATE player SET dead = 'FALSE' WHERE account_id = $killed_id AND game_id = $player->game_id");

	} else {

		// stop looping
		$result = false;

		// give table free
		$dead_ppl->unlock();

	}

} // end of while ($result)
if ($player->is_fed_protected()) $container["continue"] = "No";
if (!$debug || $container["continue"] == "Yes") {

	transfer("target");
	forward($container);

} else {

	// we cannot forward if something was printed on that page

}

?>
