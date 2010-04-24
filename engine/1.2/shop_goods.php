<?php
require_once(get_file_loc('smr_sector.inc'));
		$sector = new SMR_SECTOR($player->sector_id, SmrSession::$game_id, SmrSession::$old_account_id);
		require_once(get_file_loc("smr_port.inc"));
$player->get_relations();
// include helper file
require_once("shop_goods.inc");

// create object from port we can work with
$port = new SMR_PORT($player->sector_id, SmrSession::$game_id);

// total relations with that race (personal + global)
$relations = $player->relations[$port->race_id] + $player->relations_global_rev[$port->race_id];
if (empty($relations))
	$relations = 0;

if($relations <= -300) {
	create_error('We will not trade with our enemies!');
}

if($port->refresh_defense > time()) {
	create_error('We are still repairing damage caused during the last raid.');
}

// topic
print_topic("PORT IN SECTOR #$player->sector_id");

$container = array();
$container["url"] = "skeleton.php";
$container["body"] = "council_send_message.php";
$container["race_id"] = $port->race_id;
$container["race_name"] = $port->race_name;

print("<p>This is a level $port->level port and run by the " . create_link($container, $player->get_colored_race($port->race_id)) . ".<br>");
print("Your relations with them are " . get_colored_text($relations, $relations) . ".</p>");

print("<p>&nbsp;</p>");
$account->log(6, "Player examines port", $player->sector_id);
//The player is sent here after trading and sees this if his offer is accepted.
//You have bought/sold 300 units of Luxury Items for 1738500 credits. For your excellent trading skills you receive 220 experience points!
if (!empty($var["traded_xp"]) ||
	!empty($var["traded_amount"]) ||
	!empty($var["traded_good"]) ||
	!empty($var["traded_credits"]) ||
	!empty($var["traded_transaction"])) {

	print("<p>You have just " . $var["traded_transaction"] . " <span style=\"color:yellow;\">" . $var["traded_amount"] . "</span> units ");
	print("of <span style=\"color:yellow;\">" . $var["traded_good"] . "</span> for ");
	print("<span style=\"color:yellow;\">" . $var["traded_credits"] . "</span> credits.<br>");
	if ($var["traded_xp"] > 0)
		print("<p>For your excellent trading skills you have gained <span style=\"color:blue;\">" . $var["traded_xp"] . "</span> experience points!</p>");

// test if we are searched. (but only if we hadn't a previous trade here
} elseif ($player->controlled != $player->sector_id) {

	$db->query("SELECT * FROM port_has_goods " .
			   "WHERE game_id = $player->game_id AND " .
					 "sector_id = $player->sector_id AND " .
					 "(good_id = 5 OR good_id = 9 OR good_id = 12)");
	$base_chance = 15 - ($db->nf() * 4);

	if ($ship->ship_type_id == 23 || $ship->ship_type_id == 24 || $ship->ship_type_id == 25)
		$base_chance -= 4;

	$rand = mt_rand(1, 100);
	if ($rand <= $base_chance) {

		if (!empty($ship->cargo[5]) || !empty($ship->cargo[9]) || !empty($ship->cargo[12])) {

			//find the fine
			//get base for ports that dont happen to trade that good
			$query = new SmrMySqlDatabase();
			$query->query("SELECT * FROM good WHERE good_id = 5 OR good_id = 9 OR good_id = 12");
			$base = array();
			while ($query->next_record()) {
				$base[$query->f("good_id")] = $query->f("base_price");
			}
			$fine = $port->level * (($ship->cargo[5] * $base[5]) +
									($ship->cargo[9] * $base[9]) +
									($ship->cargo[12] * $base[12]));
			$player->credits -= $fine;
			if ($player->credits < 0) {

				// because credits is 0 it will take money from bank
				$player->bank += $player->credits;

				// set credits to zero
				$player->credits = 0;

				// leave insurance
				if ($player->bank < 5000)
					$player->bank = 5000;

			}

			print("<span style=\"color:red;\">The Federation searched your ship and illegal goods were found!</span><br>");
			print("<span style=\"color:red;\">All illegal goods have been removed from your ship and you have been fined " . number_format($fine) . " credits</span>");

			//lose align and the good your carrying along with money
			$player->alignment -= 5;

			$ship->cargo[5] = 0;
			$ship->cargo[9] = 0;
			$ship->cargo[12] = 0;
			$ship->update_cargo();
			$account->log(6, "Player gets caught with illegals", $player->sector_id);

		} else {

			print("<span style=\"color:blue;\">The Federation searched your ship and no illegal goods where found!</span>");
			$player->alignment += 1;
			$account->log(6, "Player gains alignment at port", $player->sector_id);

		}

		$player->update();

	}

}
//update controlled in db
$player->controlled = $player->sector_id;
$player->update();
$db->query("SELECT * FROM port, port_has_goods, good WHERE port.game_id = port_has_goods.game_id AND " .
														  "port.sector_id = port_has_goods.sector_id AND " .
														  "port_has_goods.good_id = good.good_id AND " .
														  "port.sector_id = $sector->sector_id AND " .
														  "port.game_id = ".SmrSession::$game_id." AND " .
														  "transaction = 'BUY' " .
													"ORDER BY good.good_id");
if ($db->nf()) {

	print("<h2>The port sells you the following:</h2>");
	print_table();
	print("<tr>");
	print("<th align=\"center\">Good</th>");
	print("<th align=\"center\">Supply</th>");
	print("<th align=\"center\">Base Price</th>");
	print("<th align=\"center\">Amount on Ship</th>");
	print("<th align=\"center\">Amount to Trade</th>");
	print("<th align=\"center\">Action</th>");
	print("</tr>");

	$container = array();
	$container["url"] = "shop_goods_processing.php";

	while ($db->next_record()) {

		$good_id = $db->f("good_id");
		$good_name = $db->f("good_name");
		$good_class = $db->f("good_class");

		// if we are good, skip evil stuff
		if ($player->alignment > -100 && ($good_id == 5 || $good_id == 9 || $good_id == 12)) continue;

		$container["good_id"] = $good_id;
		$container["good_name"] = $good_name;
		$container["good_class"] = $good_class;
		$form = create_form($container, $port->transaction[$good_id]);
		//print_form($container);

		print("<tr>");
		echo $form['form'];
		print("<td align=\"center\">$good_name</td>");
		print("<td align=\"center\">" . $port->amount[$good_id] . "</td>");
		print("<td align=\"center\">" . $port->base_price[$good_id] . "</td>");
		print("<td align=\"center\">" . $ship->cargo[$good_id] . "</td>");
		print("<td align=\"center\"><input type=\"text\" name=\"amount\" value=\"");

		if ($port->amount[$good_id] < $ship->cargo_left)
			print($port->amount[$good_id]);
		else
			print($ship->cargo_left);

		print("\" size=\"4\" id=\"InputFields\" style=\"text-align:center;\"></td>");
		print("<td align=\"center\">");
		//print_submit($port->transaction[$good_id]);
		echo $form['submit'];
		print("</td>");
		print("</form>");
		print("</tr>");


	}

	print("</table>");

	print("<p>&nbsp;</p>");

}

$db->query("SELECT * FROM port, port_has_goods, good WHERE port.game_id = port_has_goods.game_id AND " .
														  "port.sector_id = port_has_goods.sector_id AND " .
														  "port_has_goods.good_id = good.good_id AND " .
														  "port.sector_id = $sector->sector_id AND " .
														  "port.game_id = ".SmrSession::$game_id." AND " .
														  "transaction = 'SELL' " .
													"ORDER BY good.good_id");
if ($db->nf()) {

	print("<h2>The port would buy the following:</h2>");
	print_table();
	print("<tr>");
	print("<th align=\"center\">Good</th>");
	print("<th align=\"center\">Demand</th>");
	print("<th align=\"center\">Base Price</th>");
	print("<th align=\"center\">Amount on Ship</th>");
	print("<th align=\"center\">Amount to Trade</th>");
	print("<th align=\"center\">Action</th>");
	print("</tr>");

	$container = array();
	$container["url"] = "shop_goods_processing.php";

	while ($db->next_record()) {

		$good_id = $db->f("good_id");
		$good_name = $db->f("good_name");
		$good_class = $db->f("good_class");

		// if we are good, skip evil stuff
		if ($player->alignment > -100 && ($good_id == 5 || $good_id == 9 || $good_id == 12)) continue;

		$container["good_id"] = $good_id;
		$container["good_name"] = $good_name;
		$container["good_class"] = $good_class;
		print_form($container);

		print("<tr>");
		print("<td align=\"center\">$good_name</td>");
		print("<td align=\"center\">" . $port->amount[$good_id] . "</td>");
		print("<td align=\"center\">" . $port->base_price[$good_id] . "</td>");
		print("<td align=\"center\">" . $ship->cargo[$good_id] . "</td>");
		print("<td align=\"center\"><input type=\"text\" name=\"amount\" value=\"");

		if ($port->amount[$good_id] < $ship->cargo[$good_id])
			print($port->amount[$good_id]);
		else
			print($ship->cargo[$good_id]);

		print("\" size=\"4\" id=\"InputFields\" style=\"text-align:center;\"></td>");
		print("<td align=\"center\">");
		print_submit($port->transaction[$good_id]);
		print("</td>");
		print("</tr>");
		print("</form>");

	}

	print("</table>");

	print("<p>&nbsp;</p>");

}

print("<h2>Or do you want to:</h2>");

print_form(create_container("skeleton.php", "current_sector.php"));
print_submit("Leave Port");
print("<form>");

?>