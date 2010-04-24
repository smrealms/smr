<?php
require_once(get_file_loc('smr_sector.inc'));
print_topic("SECTOR DEATH RANKINGS");

include(get_file_loc('menue.inc'));
print_ranking_menue(3,0);

print("<div align=\"center\">");
print("<p>Here are the most deadly Sectors!</p>");
print("<table cellspacing=\"0\" cellpadding=\"5\" border=\"0\" class=\"standard\" width=\"60%\">");
print("<tr>");
print("<th>Rank</th>");
print("<th>Sector</th>");
print("<th>Battles</th>");
print("</tr>");

$db->query("SELECT * FROM sector WHERE game_id = $player->game_id ORDER BY battles DESC, sector_id LIMIT 10");

$rank = 0;
while ($db->next_record()) {

	// get current player
	$curr_sector = new SMR_SECTOR($db->f("sector_id"), $player->game_id, $player->account_id);

	// increase rank counter
	$rank++;

	print("<tr>");

	print("<td valign=\"top\" align=\"center\"");
	if ($player->sector_id == $curr_sector->sector_id)
		print(" style=\"font-weight:bold;\"");
	print(">$rank</td>");

	print("<td valign=\"top\" align=\"center\"");
	if ($player->sector_id == $curr_sector->sector_id)
		print(" style=\"font-weight:bold;\"");
	print(">$curr_sector->sector_id</td>");

	print("<td valign=\"top\" align=\"center\"");
	if ($player->sector_id == $curr_sector->sector_id)
		print(" style=\"font-weight:bold;\"");
	print(">" . number_format($curr_sector->battles) . "</td>");

	print("</tr>");

}

print("</table>");
$action = $_REQUEST['action'];
if ($action == "Show") {

	$min_rank = $_POST["min_rank"];
	$max_rank = $_POST["max_rank"];

} else {

	$min_rank = $our_rank - 5;
	$max_rank = $our_rank + 5;

}

if ($min_rank < 0) {

	$min_rank = 1;
	$max_rank = 10;

}

// how many alliances are there?
$db->query("SELECT max(sector_id) FROM sector WHERE game_id = $player->game_id");
if ($db->next_record())
	$total_sector = $db->f("max(sector_id)");

if ($max_rank > $total_sector)
	$max_rank = $total_sector;

$container = array();
$container["url"]		= "skeleton.php";
$container["body"]		= "rankings_sector_kill.php";
$container["min_rank"]	= $min_rank;
$container["max_rank"]	= $max_rank;

print_form($container);
print("<p><input type=\"text\" name=\"min_rank\" value=\"$min_rank\" size=\"3\" id=\"InputFields\" style=\"text-align:center;\">&nbsp;-&nbsp;<input type=\"text\" name=\"max_rank\" value=\"$max_rank\" size=\"3\" id=\"InputFields\" style=\"text-align:center;\">&nbsp;");
print_submit("Show");
print("</p></form>");
print("<table cellspacing=\"0\" cellpadding=\"5\" border=\"0\" class=\"standard\" width=\"95%\">");
print("<tr>");
print("<th>Rank</th>");
print("<th>Sector</th>");
print("<th>Battles</th>");
print("</tr>");

$db->query("SELECT * FROM sector WHERE game_id = $player->game_id ORDER BY battles DESC, sector_id LIMIT " . ($min_rank - 1) . ", " . ($max_rank - $min_rank + 1));

$rank = $min_rank - 1;
while ($db->next_record()) {

	// get current player
	$curr_sector = new SMR_SECTOR($db->f("sector_id"), $player->game_id, $player->account_id);

	// increase rank counter
	$rank++;

	print("<tr>");

	print("<td valign=\"top\" align=\"center\"");
	if ($player->sector_id == $curr_sector->sector_id)
		print(" style=\"font-weight:bold;\"");
	print(">$rank</td>");

	print("<td valign=\"top\" align=\"center\"");
	if ($player->sector_id == $curr_sector->sector_id)
		print(" style=\"font-weight:bold;\"");
	print(">$curr_sector->sector_id</td>");

	print("<td valign=\"top\" align=\"center\"");
	if ($player->sector_id == $curr_sector->sector_id)
		print(" style=\"font-weight:bold;\"");
	print(">" . number_format($curr_sector->battles) . "</td>");

	print("</tr>");

}

print("</table>");
print("</div>");

?>