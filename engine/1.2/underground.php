<?php

if ($player->alignment >= 100) {

	print_error("You are not allowed to come in here!");
	return;

}

print_topic("Underground HQ");

include(get_file_loc('menue.inc'));
print_ug_menue();

$db2 = new SmrMySqlDatabase();
$db->query("SELECT * FROM bounty WHERE game_id = $player->game_id AND type = 'UG' AND claimer_id = 0 ORDER BY amount DESC");

if ($db->nf()) {

	print("Most Wanted by the Underground<br><br>");
	print_table();
	print("<tr>");
	print("<th>Player Name</th>");
	print("<th>Bounty Amount</th>");
	print("</tr>");

	while ($db->next_record()) {

		$id = $db->f("account_id");
		$db2->query("SELECT * FROM player WHERE game_id = $player->game_id AND account_id = $id");
		if ($db2->next_record()) {

			$name = stripslashes($db2->f("player_name"));
			$amount = $db->f("amount");
			print("<tr>");
			print("<td align=\"center\"><font color=yellow>$name</font></td>");
			print("<td align=\"center\"><font color=red> " . number_format($amount) . " </font></td>");
			print("</tr>");

		}

	}

	print("</table>");

}


if ($player->alignment <= 99 && $player->alignment >= -100) {

	print_form(create_container("government_processing.php", ""));
	print_submit("Become a gang member");
	print("</form>");

}
?>