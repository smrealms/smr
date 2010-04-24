<?php
$player->get_relations();
print_topic("WEAPON DEALER");
$db2 = new SmrMySqlDatabase();
$db->query("SELECT * FROM location, location_sells_weapons, location_type, weapon_type " .
					"WHERE location.sector_id = $player->sector_id AND " .
    					  "location.game_id = ".SmrSession::$game_id." AND " .
    					  "location.location_type_id = location_sells_weapons.location_type_id AND " .
    					  "location_sells_weapons.location_type_id = location_type.location_type_id AND " .
    					  "location_sells_weapons.weapon_type_id = weapon_type.weapon_type_id");

if ($db->nf() > 0 ) {

	print("<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" class=\"standard\">");
	print("<tr>");
	print("<th align=\"center\">Name</th>");
	print("<th align=\"center\">Shield Damage</th>");
	print("<th align=\"center\">Armor Damage</th>");
	print("<th align=\"center\">Accuracy</th>");
	print("<th align=\"center\">Race</th>");
	print("<th align=\"center\">Power Level</th>");
	print("<th align=\"center\">Cost</th>");
	print("<th align=\"center\">Action</th>");
	print("</tr>");

	while ($db->next_record()) {

		$weapon_name = $db->f("weapon_name");
		$weapon_type_id = $db->f("weapon_type_id");
		$shield_damage = $db->f("shield_damage");
		$armor_damage  = $db->f("armor_damage");
		$accuracy = $db->f("accuracy");
        $db2->query("SELECT * FROM weapon_type WHERE weapon_type_id = $weapon_type_id");
        $db2->next_record();
        $race_id = $db2->f("race_id");
		$power_level = $db->f("power_level");
		$cost = $db->f("cost");
		$buyer_restriction = $db->f("buyer_restriction");

        $db2->query("SELECT * FROM race WHERE race_id = $race_id");
        $db2->next_record();
        $weapon_race = $db2->f("race_name");

		$container = array();
		$container["url"] = "shop_weapon_processing.php";
        if ($race_id !=1) {

        	if ($player->relations_global_rev[$race_id] + $player->relations[$race_id] < 300)
        		$container["cant_buy"] = "Yes";

        }
		$container["weapon_id"] = $weapon_type_id;
		$container["power_level"] = $power_level;
		$container["buyer_restriction"] = $buyer_restriction;
		$container["cost"] = $cost;
		$container["weapon_type_id"] = $weapon_type_id;
		print_form($container);

		print("<tr>");
		print("<td align=\"center\">$weapon_name</td>");
		print("<td align=\"center\">$shield_damage</td>");
		print("<td align=\"center\">$armor_damage</td>");
		print("<td align=\"center\">$accuracy</td>");
		print("<td align=\"center\">$weapon_race</td>");
		print("<td align=\"center\">$power_level</td>");
		print("<td align=\"center\">$cost</td>");
		print("<td align=\"center\">");
		print_submit("Buy");
		print("</td>");
		print("</tr>");
		print("</form>");

	}

	print("</table>");

}

if (sizeof($ship->weapon) > 0) {

	print_topic("SELL WEAPONS");

	print("<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" class=\"standard\">");
	print("<tr>");
	print("<th align=\"center\">Name</th>");
	print("<th align=\"center\">Cash</th>");
	print("<th align=\"center\">Action</th>");
	print("</tr>");

	foreach ($ship->weapon as $order_id => $weapon_name) {

		$db->query("SELECT * FROM weapon_type WHERE weapon_name = '$weapon_name'");
		while ($db->next_record()) {

			$weapon_type_id = $db->f("weapon_type_id");
			$cost = $db->f("cost") / 2;

			$container = array();
			$container["url"] = "shop_weapon_processing.php";
			$container["order_id"] = $order_id;
			$container["cash_back"] = $cost;
			$container["weapon_type_id"] = $weapon_type_id;
			print_form($container);

			print("<tr>");
			print("<td align=\"center\">$weapon_name</td>");
			print("<td align=\"center\">$cost</td>");
			print("<td align=\"center\">");
			print_submit("Sell");
			print("</td>");
			print("</tr>");
			print("</form>");

		}

	}

	print("</table>");

}

?>