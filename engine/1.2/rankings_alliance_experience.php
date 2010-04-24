<?php
print_topic("ALLIANCE EXPERIENCE RANKINGS");
include(get_file_loc('menue.inc'));
print_ranking_menue(1, 0);

$db->query("SELECT player.alliance_id as alliance_id, sum( experience ) AS alliance_exp, count( * ) AS members, alliance_name AS name
				FROM player, alliance
				WHERE player.game_id = " . $player->game_id . " 
				AND player.game_id = alliance.game_id
				AND alliance.alliance_id = player.alliance_id
				GROUP BY player.alliance_id
				ORDER BY alliance_exp DESC");
$alliances = array();
while ($db->next_record()) {
	$alliances[$db->f("alliance_id")] = array(stripslashes($db->f("name")), $db->f("alliance_exp"), $db->f("members"));
	if ($db->f("alliance_id") == $player->alliance_id) $ourRank = sizeof($alliances);
}
// how many alliances are there?
$numAlliances = sizeof($alliances);

print("<div align=\"center\">");
print("<p>Here are the rankings of alliances by their experience.</p>");
if ($player->alliance_id > 0)
	print("<p>Your alliance is ranked $ourRank out of $numAlliances alliances.</p>");

print("<table cellspacing=\"0\" cellpadding=\"5\" border=\"0\" class=\"standard\" width=\"95%\">");
print("<tr>");
print("<th>Rank</th>");
print("<th>Alliance</th>");
print("<th>Total Experience</th>");
print("<th>Average Experience</th>");
print("<th>Total Traders</th>");
print("</tr>");

$rank = 0;
foreach ($alliances as $id => $infoArray) {
	$rank++;
	$currAllianceName = $infoArray[0];
	$totalExp = $infoArray[1];
	$members = $infoArray[2];
	if ($rank > 10) break;
	print("<tr>");
	$style = 'style="vertical-align:top;text-align:center;';
	$style2 = '';
	if($player->alliance_id == $id)
		$style2 .= 'font-weight:bold;';
	$style .= $style2 . '"';

	print("<td $style>$rank</td>");

	echo '<td style="vertical-align:top;' . $style2 . '">';
	$container = create_container('skeleton.php','alliance_roster.php');
	$container["alliance_id"]	= $id;
	print_link($container, $currAllianceName);
	print("</td>");

	print("<td $style>" . number_format($totalExp) . "</td>");
	print("<td $style>" . number_format(round($totalExp / $members)) . "</td>");
	print("<td $style>" . $members . "</td>");
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

$container = create_container('skeleton.php','rankings_alliance_experience.php');
$container["min_rank"]	= $min_rank;
$container["max_rank"]	= $max_rank;

print_form($container);
print("<p><input type=\"text\" name=\"min_rank\" value=\"$min_rank\" size=\"3\" id=\"InputFields\" style=\"text-align:center;\">&nbsp;-&nbsp;<input type=\"text\" name=\"max_rank\" value=\"$max_rank\" size=\"3\" id=\"InputFields\" style=\"text-align:center;\">&nbsp;");
print_submit("Show");
print("</p></form>");

print("<table cellspacing=\"0\" cellpadding=\"5\" border=\"0\" class=\"standard\" width=\"95%\">");
print("<tr>");
print("<th>Rank</th>");
print("<th>Alliance</th>");
print("<th>Total Experience</th>");
print("<th>Average Experience</th>");
print("<th>Total Traders</th>");
print("</tr>");

$rank = 0;
foreach ($alliances as $id => $infoArray) {
	$rank++;
	if ($rank < $min_rank) continue;
	elseif ($rank > $max_rank) break;
	$currAllianceName = $infoArray[0];
	$totalExp = $infoArray[1];
	$members = $infoArray[2];
	
	print("<tr>");
	$style = 'style="vertical-align:top;text-align:center;';
	$style2 = '';
	if($player->alliance_id == $id)
		$style2 .= 'font-weight:bold;';
	$style .= $style2 . '"';

	print("<td $style>$rank</td>");

	echo '<td style="vertical-align:top;' . $style2 . '">';
	$container = create_container('skeleton.php','alliance_roster.php');
	$container["alliance_id"]	= $id;
	print_link($container, $currAllianceName);
	print("</td>");

	print("<td $style>" . number_format($totalExp) . "</td>");
	print("<td $style>" . number_format(round($totalExp / $members)) . "</td>");
	print("<td $style>" . $members . "</td>");
	print("</tr>");

}

print("</table>");
print("</div>");

?>