<?php

print_topic("HARDWARE SHOP");

$db->query("SELECT * FROM location, location_sells_hardware, location_type, hardware_type " .
					"WHERE location.sector_id = $player->sector_id AND " .
						  "location.game_id = ".SmrSession::$game_id." AND " .
						  "location.location_type_id = location_sells_hardware.location_type_id AND " .
						  "location_sells_hardware.location_type_id = location_type.location_type_id AND " .
						  "location_sells_hardware.hardware_type_id = hardware_type.hardware_type_id");

if ($db->nf() > 0 ) {

	print("<table cellspacing=\"0\" cellpadding=\"3\" border=\"0\" class=\"standard\">");
	print("<tr>");
	print("<th align=\"center\">Name</th>");
	print("<th align=\"center\">Purchase Amount</th>");
	print("<th>&nbsp;</th>");
	print("<th align=\"center\">Unit Cost</th>");
	print("<th>&nbsp;</th>");
	print("<th align=\"center\" width=\"75\">Totals</th>");
	print("<th align=\"center\">Action</th>");
	print("</tr>");

	$form = 0;

	while ($db->next_record()) {

		$hardware_name = $db->f("hardware_name");
		$hardware_type_id = $db->f("hardware_type_id");
		$cost = $db->f("cost");

		$amount = $ship->max_hardware[$hardware_type_id] - $ship->hardware[$hardware_type_id];

		print("<script type=\"text/javascript\" language=\"JavaScript\">\n");
		print("function recalc_" . $hardware_type_id . "_onkeyup() {\n");
		//print("window.document.form_$hardware_type_id.total.value = window.document.form_$hardware_type_id.amount.value * $cost;\n");
		print("window.document.forms[$form].total.value = window.document.forms[$form].amount.value * $cost;\n");
		print("}\n");
		print("</script>");

		$form++;

		$container = array();
		$container["url"] = "shop_hardware_processing.php";
		$container["hardware_id"] = $hardware_type_id;
		$container["hardware_name"] = $hardware_name;
		$container["cost"] = $cost;

		print_form($container);
		print("<tr>");
		print("<td align=\"center\">$hardware_name</td>");
		print("<td align=\"center\"><input type=\"text\" name=\"amount\" value=\"$amount\" size=\"5\" onKeyUp=\"recalc_" . $hardware_type_id . "_onkeyup()\" id=\"InputFields\" style=\"text-align:center;\"></td>");
		print("<td>*</td>");
		print("<td align=\"center\">$cost</td>");
		print("<td>=</td>");
		print("<td align=\"center\"><input type=\"text\" name=\"total\" value=\"" . $amount * $cost . "\" size=\"7\" id=\"InputFields\" style=\"text-align:center;\"></td>");
		print("<td align=\"center\">");
		print_submit("Buy");
		print("</td>");
		print("</tr>");
		print("</form>");

	}

	print("</table>");

} else print("I have nothing to sell to you. Get out of here!");

?>