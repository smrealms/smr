<?php

$player->get_relations();

include(get_file_loc('race_voting.php'));
include(get_file_loc("council.inc"));
include(get_file_loc('menue.inc'));

print_topic("RULING COUNCIL OF $player->race_name");

$president = getPresident($player->race_id);

print_council_menue($player->race_id, $president);

// determine for what we voted
$db->query("SELECT * FROM player_votes_relation " .
		   "WHERE account_id = $player->account_id AND " .
				 "game_id = $player->game_id");
if ($db->next_record()) {

	$voted_for_race	= $db->f("race_id_2");
	$voted_for		= $db->f("action");

}

print("<table border=\"0\" class=\"standard\" cellspacing=\"0\" align=\"center\" width=\"75%\">");
print("<tr>");
print("<th>Race</th>");
print("<th>Vote</th>");
print("<th>Our Relation<br>with them</th>");
print("<th>Their Relation<br>with us</th>");
print("</tr>");
$db->query("SELECT * FROM race " .
		   "WHERE race_id != $player->race_id AND " .
				 "race_id > 1");
while($db->next_record()) {

	$race_id	= $db->f("race_id");
	$race_name	= $db->f("race_name");

	print("<tr>");
	print("<td align=\"center\">" . $player->get_colored_race($race_id) . "</td>");

	$container = array();
	$container["url"]		= "council_vote_processing.php";
	$container["race_id"]	= $race_id;

	print_form($container);
	print("<td align=\"center\">");
	if ($voted_for_race == $race_id && $voted_for == "INC")
		print_submit_style("Increase", "background-color:green;");
	else
		print_submit("Increase");
	print("&nbsp;");
	if ($voted_for_race == $race_id && $voted_for == "DEC")
		print_submit_style("Decrease", "background-color:green;");
	else
		print_submit("Decrease");
	print("</td>");
	print("</form>");

	$relation = $player->relations_global[$race_id];
	print("<td align=\"center\">" . get_colored_text($relation, $relation) . "</td>");

	$relation = $player->relations_global_rev[$race_id];
	print("<td align=\"center\">" . get_colored_text($relation, $relation) . "</td>");

	print("</tr>");

}

print("</table>");

print("<p>&nbsp;</p>");

$curr_time = time();

$db->query("SELECT * FROM race_has_voting " .
		   "WHERE $curr_time < end_time AND " .
				 "game_id = $player->game_id AND " .
				 "race_id_1 = $player->race_id");
if ($db->nf() > 0) {

	print("<table border=\"0\" class=\"standard\" cellspacing=\"0\" align=\"center\" width=\"65%\">");
	print("<tr>");
	print("<th>Race</th>");
	print("<th>Treaty</th>");
	print("<th>Option</th>");
	print("<th>Currently</th>");
	print("<th>End Time</th>");
	print("</tr>");

	$db2 = new SmrMySqlDatabase();

	while ($db->next_record()) {

		$race_id_2	= $db->f("race_id_2");
		$type		= $db->f("type");
		$end_time	= $db->f("end_time");

		print("<tr>");
		print("<td align=\"center\">" . $player->get_colored_race($race_id_2) . "</td>");
		print("<td align=\"center\">$type</td>");

		$container = array();
		$container["url"]		= "council_vote_processing.php";
		$container["race_id"]	= $race_id_2;

		print_form($container);

		$db2->query("SELECT * FROM player_votes_pact " .
					"WHERE account_id = $player->account_id AND " .
						  "game_id = $player->game_id AND " .
						  "race_id_1 = $player->race_id AND " .
						  "race_id_2 = $race_id_2");
		if ($db2->next_record())
			$voted_for = $db2->f("vote");
		else
			$voted_for = "";

		print("<td nowrap=\"nowrap\" align=\"center\">");
		if ($voted_for == "YES")
			print_submit_style("Yes", "background-color:green;");
		else
			print_submit("Yes");
		print("&nbsp;");
		if ($voted_for == "NO")
			print_submit_style("No", "background-color:green;");
		else
			print_submit("No");
		if ($president->account_id == $player->account_id) {

			print("&nbsp;");
			print_submit("Veto");

		}
		print("</td>");

		// get 'yes' votes
		$db2->query("SELECT * FROM player_votes_pact " .
					"WHERE game_id = $player->game_id AND " .
						  "race_id_1 = $player->race_id AND " .
						  "race_id_2 = $race_id_2 AND " .
						  "vote = 'YES'");
		$yes_votes = $db2->nf();

		// get 'no' votes
		$db2->query("SELECT * FROM player_votes_pact " .
					"WHERE game_id = $player->game_id AND " .
						  "race_id_1 = $player->race_id AND " .
						  "race_id_2 = $race_id_2 AND " .
						  "vote = 'NO'");
		$no_votes = $db2->nf();

		print("<td align=\"center\">$yes_votes / $no_votes</td>");
		print("<td nowrap=\"nowrap\"align=\"center\">" . date("n/j/Y", $end_time) . "<br>" . date("g:i:s A", $end_time) . "</td>");
		print("</form>");
		print("</tr>");

	}

	print("</table>");

}

?>