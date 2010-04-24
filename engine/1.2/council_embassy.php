<?php

include(get_file_loc("council.inc"));
include(get_file_loc('menue.inc'));

print_topic("RULING COUNCIL OF $player->race_name");

print_council_menue($player->race_id, getPresident($player->race_id));

print("<table border=\"0\" class=\"standard\" cellspacing=\"0\" align=\"center\" width=\"50%\">");
print("<tr>");
print("<th>Race</th>");
print("<th>Treaty</th>");
print("</tr>");

$db2 = new SmrMySqlDatabase();

$db->query("SELECT * FROM race " .
		   "WHERE race_id != $player->race_id AND " .
				 "race_id > 1");
while($db->next_record()) {

	$race_id	= $db->f("race_id");
	$race_name	= $db->f("race_name");

	$db2->query("SELECT * FROM race_has_voting " .
				"WHERE game_id = $player->game_id AND " .
					  "race_id_1 = $player->race_id AND " .
					  "race_id_2 = $race_id");
	if ($db2->nf() > 0) continue;

	print("<tr>");
	print("<td align=\"center\">" . $player->get_colored_race($race_id) . "</td>");

	$container = array();
	$container["url"]		= "council_embassy_processing.php";
	$container["race_id"]	= $race_id;

	print_form($container);
	print("<td align=\"center\">");
	print_submit("Peace");
	print("&nbsp;");
	print_submit("War");
	print("</td>");
	print("</form>");

	print("</tr>");

}

print("</table>");

?>