<?php

print_topic("DUMP CARGO");

print("Enter the amount of cargo you wish to jettison.<br>");
print("Please keep in mind that you will lose experience and one turn!<br><br>");

$db->query("SELECT * FROM ship_has_cargo NATURAL JOIN good " .
		   "WHERE account_id = $player->account_id AND " .
				 "game_id = $player->game_id");
if ($db->nf()) {

	print_table();
	print("<tr>");
	print("<th>Good</th>");
	print("<th>Amount to Drop</th>");
	print("<th>Action</th>");
	print("</tr>");

	while ($db->next_record()) {

		$good_id	= $db->f("good_id");
		$good_name	= $db->f("good_name");
		$amount		= $db->f("amount");

		$container = array();
		$container["url"] = "cargo_dump_processing.php";
		$container["good_id"] = $good_id;
		$container["good_name"] = $good_name;

		print_form($container);
		print("<tr>");
		print("<td align=\"center\">$good_name</td>");
		print("<td align=\"center\"><input type=\"text\" name=\"amount\" value=\"$amount\" maxlength=\"5\" size=\"5\" id=\"InputFields\" style=\"text-align:center;\">");
		print("<td align=\"center\">");
		print_submit("Dump");
		print("</td>");
		print("</tr>");
		print("</form>");

	}

	print("</table>");

} else
	print("You have no cargo to dump!");

?>