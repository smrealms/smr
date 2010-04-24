<?php

print_topic("VIEW FORCES");

//allow for ordering of forces
if (!isset($var["seq"]))
    $order = "ASC";
else
    $order = $var["seq"];
    
if (!isset($var["category"]))
    $category = "sector_id";
else
    $category = $var["category"];
$db->query("SELECT * FROM sector_has_forces WHERE owner_id = $player->account_id AND game_id = ".SmrSession::$game_id." ORDER BY $category $order");
$db2 = new SmrMySqlDatabase();
if ($db->nf() > 0) {
	
	$container = array();
	$container["url"] = "skeleton.php";
	$container["body"] = "forces_list.php";
	if ($order == "ASC")
		$container["seq"] = "DESC";
    else
        $container["seq"] = "ASC";
	print_table();
	print("<tr>");
	$container["category"] = "sector_id";
    print("<th align=\"center\">");
    print_link($container, "<span style=\"color:#80C870;\">Sector ID</span>");
	print("</th>");
	$container["category"] = "combat_drones";
    print("<th align=\"center\">");
    print_link($container, "<span style=\"color:#80C870;\">Combat Drones</span>");
	print("</th>");
	$container["category"] = "scout_drones";
    print("<th align=\"center\">");
    print_link($container, "<span style=\"color:#80C870;\">Scout Drones</span>");
	print("</th>");
	$container["category"] = "mines";
    print("<th align=\"center\">");
    print_link($container, "<span style=\"color:#80C870;\">Mines</span>");
	print("</th>");
	$container["category"] = "expire_time";
    print("<th align=\"center\">");
    print_link($container, "<span style=\"color:#80C870;\">Expire time</span>");
	print("</th>");
	print("</tr>");

	while ($db->next_record()) {

		$force_sector	= $db->f("sector_id");
		$db2->query("SELECT * FROM sector WHERE sector_id = $force_sector AND game_id = $player->game_id");
		$db2->next_record();
		$gal_id			= $db2->f("galaxy_id");
		$db2->query("SELECT * FROM galaxy WHERE galaxy_id = $gal_id");
		$db2->next_record();
		$galaxy_name = $db2->f("galaxy_name");
		$force_sd		= $db->f("scout_drones");
		$force_cd		= $db->f("combat_drones");
		$force_mine		= $db->f("mines");
		$force_time		= $db->f("expire_time");
		
		print("<tr>");
		print("<td align=\"center\">$force_sector ($galaxy_name)</td>");
		print("<td align=\"center\">$force_cd</td>");
		print("<td align=\"center\">$force_sd</td>");
		print("<td align=\"center\">$force_mine</td>");
		print("<td align=\"center\">" . date("n/j/Y g:i:s A", $force_time) . "</td>");
		print("</tr>");

	}

	print("</table>");
}

else
	print("You have no deployed forces");

?>