<?php
print_topic("ALLIANCE DEATH RANKINGS");
include(get_file_loc('menue.inc'));
print_ranking_menue(1, 2);

$db->query("SELECT alliance_id, alliance_name, alliance_deaths, leader_id FROM alliance
			WHERE game_id = $player->game_id ORDER BY alliance_deaths DESC, alliance_name");
$alliances = array();
while ($db->next_record()) {
	$alliances[$db->f("alliance_id")] = array(stripslashes($db->f("alliance_name")), $db->f("alliance_deaths"), $db->f("leader_id"));
	if ($db->f("alliance_id") == $player->alliance_id) $ourRank = sizeof($alliances);
}

// how many alliances are there?
$numAlliances = sizeof($alliances);

print("<div align=\"center\">");
print("<p>Here are the rankings of alliances by their deaths.</p>");
if ($player->alliance_id > 0)
    print("<p>Your alliance is ranked $ourRank out of $numAlliances alliances.</p>");

print("<table cellspacing=\"0\" cellpadding=\"5\" border=\"0\" class=\"standard\" width=\"95%\">");
print("<tr>");
print("<th>Rank</th>");
print("<th>Alliance</th>");
print("<th>Deaths</th>");
print("</tr>");

$rank = 0;
foreach($alliances as $id => $infoArray) {

    // get current alliance
    $currAllianceName = $infoArray[0];
    $numDeaths = $infoArray[1];
    $out = (!$infoArray[2]);
	$rank++;
	if ($rank > 10) break;
	print("<tr>");
	print("<td valign=\"top\" align=\"center\"");
	if ($player->alliance_id == $id)
	    print(" style=\"font-weight:bold;\"");
	elseif ($out)
		print(" style=\"color:red;\"");
	print(">$rank</td>");
	
	print("<td valign=\"top\"");
	if ($player->alliance_id == $id)
	    print(" style=\"font-weight:bold;\"");
	elseif ($out)
		print(" style=\"color:red;\"");
	print(">");
	$container = create_container('skeleton.php','alliance_roster.php');
	$container["alliance_id"]    = $id;
	if ($out)
		print($currAllianceName);
	else
		print_link($container, $currAllianceName);
	print("</td>");
	
	print("<td valign=\"top\" align=\"right\"");
	if ($player->alliance_id == $id)
	    print(" style=\"font-weight:bold;\"");
	if ($out)
		print(" style=\"color:red;\"");
	print(">" . number_format($numDeaths) . "</td>");
	
	print("</tr>");

}
print("</table>");

$action = $_REQUEST['action'];
if ($action == "Show") {
    $min_rank = $_POST["min_rank"];
    $max_rank = $_POST["max_rank"];
} else {
    $min_rank = $ourRank - 5;
    $max_rank = $ourRank + 5;
}
if ($min_rank <= 0) {
    $min_rank = 1;
    $max_rank = 10;
}
if ($max_rank > $numAlliances)
    $max_rank = $numAlliances;

$container = create_container('skeleton.php','rankings_alliance_death.php');
$container["min_rank"]    = $min_rank;
$container["max_rank"]    = $max_rank;

print_form($container);
print("<p><input type=\"text\" name=\"min_rank\" value=\"$min_rank\" size=\"3\" id=\"InputFields\" style=\"text-align:center;\">&nbsp;-&nbsp;<input type=\"text\" name=\"max_rank\" value=\"$max_rank\" size=\"3\" id=\"InputFields\" style=\"text-align:center;\">&nbsp;");
print_submit("Show");
print("</p></form>");
print("<table cellspacing=\"0\" cellpadding=\"5\" border=\"0\" class=\"standard\" width=\"95%\">");
print("<tr>");
print("<th>Rank</th>");
print("<th>Alliance</th>");
print("<th>Deaths</th>");
print("</tr>");

$rank=0;
foreach ($alliances as $id => $infoArray) {
	$rank++;
	if ($rank < $min_rank) continue;
	elseif ($rank > $max_rank) break;
	// get current alliance
	$currAllianceName = $infoArray[0];
	$numDeaths = $infoArray[1];
	$out = (!$infoArray[2]);
	
	print("<tr>");
	print("<td valign=\"top\" align=\"center\"");
	if ($player->alliance_id == $id)
		print(" style=\"font-weight:bold;\"");
	elseif ($out)
		print(" style=\"color:red;\"");
	print(">$rank</td>");

	print("<td valign=\"top\"");
	if ($player->alliance_id == $id)
		print(" style=\"font-weight:bold;\"");
	elseif ($out)
		print(" style=\"color:red;\"");
	print(">");
	$container = create_container('skeleton.php','alliance_roster.php');
	$container["alliance_id"]    = $id;
	if ($out)
		print($currAllianceName);
	else
		print_link($container, $currAllianceName);
	print("</td>");
	
	print("<td valign=\"top\" align=\"right\"");
	if ($player->alliance_id == $id)
		print(" style=\"font-weight:bold;\"");
	if ($out)
		print(" style=\"color:red;\"");
	print(">" . number_format($numDeaths) . "</td>");
	print("</tr>");
}
print("</table>");

print("</div>");

?>