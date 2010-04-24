<?php
require_once(get_file_loc('smr_alliance.inc'));
print_topic("ALLIANCE VS ALLIANCE RANKINGS");

include(get_file_loc('menue.inc'));
print_ranking_menue(1, 3);
$db2 = new SmrMySqlDatabase();
$container = array();
$container["url"] = "skeleton.php";
$container["body"] = "rankings_alliance_vs_alliance.php";

print_form($container);

if (isset($_POST["alliancer"])) $alliancer = $_POST["alliancer"];
print("<div align=\"center\">");
print("<p>Here are the rankings of alliances vs other alliances<br>");
print("Click on an alliances name for more detailed death stats.</p>");

print("<table cellspacing=\"0\" cellpadding=\"5\" class=\"standard\" width=\"95%\">");
print("<tr>");
print("<th rowspan=\"9\">Killed</th><th colspan=\"8\">Killers</th></tr><tr><td>&nbsp</td>");
if (empty($alliancer)) {
	
	$alliance_vs = array();
	$db->query("SELECT * FROM alliance WHERE game_id = $player->game_id ORDER BY alliance_kills DESC, alliance_name LIMIT 5");
	while ($db->next_record()) $alliance_vs[] = $db->f("alliance_id");
	//print("emtpy $alliancer");
	
} else $alliance_vs = $alliancer;
$alliance_vs[] = 0;

foreach ($alliance_vs as $key => $id) {
	
	// get current alliance
	$curr_alliance_id = $id;
	if ($id > 0) {
		
	    $curr_alliance = new SMR_ALLIANCE($id, SmrSession::$game_id);
		$db2->query("SELECT * FROM player WHERE alliance_id = $id AND game_id = ".SmrSession::$game_id);
		if ($db2->nf() == 0) $out = TRUE;
		else $out = FALSE;
		
		print("<td width=15% valign=\"top\"");
		if ($player->alliance_id == $curr_alliance_id)
		print(" style=\"font-weight:bold;\"");
		print(">");
		/*$container = array();
		$container["url"]             = "skeleton.php";
		$container["body"]             = "alliance_roster.php";
		$container["alliance_id"]    = $curr_alliance_id;
		print_link($container, "$curr_alliance->alliance_name");*/
		print("<select name=alliancer[] style=width:105>");
		$db->query("SELECT * FROM alliance WHERE game_id = $player->game_id AND alliance_deaths > 0 OR alliance_kills > 0 ORDER BY alliance_name");
		while ($db->next_record()) {
			
			$curr_alliance = new SMR_ALLIANCE($db->f("alliance_id"), SmrSession::$game_id);
			print("<option value=" . $db->f("alliance_id"));
			if ($id == $db->f("alliance_id"))
				print(" selected");
			print(">" . $curr_alliance->alliance_name . "</option>");
			
		}
		print("$curr_alliance->alliance_name");
		print("</td>");
		
	}
	//$alliance_vs[] = $curr_alliance_id;

}
print("<td width=10% valign=\"top\">None</td>");
print("</tr>");
//$db->query("SELECT * FROM alliance WHERE game_id = $player->game_id ORDER BY alliance_kills DESC, alliance_name LIMIT 5");
foreach ($alliance_vs as $key => $id) {
	
	print("<tr>");
	// get current alliance
	$curr_id = $id;
	if ($id > 0) {
		
		$curr_alliance = new SMR_ALLIANCE($id, SmrSession::$game_id);
		$db2->query("SELECT * FROM player WHERE alliance_id = $curr_id AND game_id = ".SmrSession::$game_id);
		if ($db2->nf() == 0) $out = TRUE;
		else $out = FALSE;
		
		print("<td width=10% valign=\"top\"");
		if ($player->alliance_id == $curr_alliance->alliance_id)
			print(" style=\"font-weight:bold;\"");
		if ($out)
			print(" style=\"color:red;\"");
		print(">");
		$container1 = array();
		$container1["url"]            = "skeleton.php";
		$container1["body"]           = "rankings_alliance_vs_alliance.php";
		$container1["alliance_id"]    = $curr_alliance->alliance_id;
		print_link($container1, "$curr_alliance->alliance_name");
		//print("$curr_alliance->alliance_name");
		print("</td>");
		
	} else {
		
		$container1 = array();
		$container1["url"]            = "skeleton.php";
		$container1["body"]           = "rankings_alliance_vs_alliance.php";
		$container1["alliance_id"]    = 0;
		print("<td width=10% valign=\"top\">");
		print_link($container1, "None");
		print("</td>");
		
	}
	
	foreach ($alliance_vs as $key => $id) {
		
		$db2->query("SELECT * FROM player WHERE alliance_id = $id AND game_id = ".SmrSession::$game_id);
		if ($db2->nf() == 0) $out2 = TRUE;
		else $out2 = FALSE;
		$db2->query("SELECT * FROM alliance_vs_alliance WHERE alliance_id_2 = $curr_id AND " .
					"alliance_id_1 = $id AND game_id = $player->game_id");
		if ($curr_id == $id && $id != 0) {
			
			if (($out || $out2))
				print("<td style=\"color:red;\">-");
			elseif ($id == $player->alliance_id || $curr_id == $player->alliance_id)
				print("<td style=\"font-weight:bold;\">-");
			else print("<td>-");
			
		} elseif ($db2->next_record()) {
			
			print("<td");
			if (($out || $out2) && ($id == $player->alliance_id || $curr_id == $player->alliance_id))
				print(" style=\"font-weight:bold;color:red;\"");
			elseif ($out || $out2)
				print(" style=\"color:red;\"");
			elseif ($id == $player->alliance_id || $curr_id == $player->alliance_id) print(" style=\"font-weight:bold;\"");
			print(">");
			$db2->p("kills");
			
		} else {
			
			print("<td");
			if (($out || $out2) && ($id == $player->alliance_id || $curr_id == $player->alliance_id))
				print(" style=\"font-weight:bold;color:red;\"");
			elseif ($out || $out2)
				print(" style=\"color:red;\"");
			elseif ($id == $player->alliance_id || $curr_id == $player->alliance_id) print(" style=\"font-weight:bold;\"");
			print(">");
			print("0");
			
		}
		print("</td>");
		
	}

    print("</tr>");

}

print("</table>");

print("<br>");
print_submit("Show");
print("</form>");
print("</div>");

if (isset($var["alliance_id"])) {
	
	print("<table align=\"center\"><tr><td width=\"45%\" align=\"center\" valign=\"top\">");
	$main_alliance = new SMR_ALLIANCE($var["alliance_id"], SmrSession::$game_id);
	$db->query("SELECT * FROM alliance_vs_alliance WHERE alliance_id_1 = $var[alliance_id] " .
				"AND game_id = $player->game_id ORDER BY kills DESC");
	if ($db->nf() > 0) {
		
		print("<div align=\"center\">Kills for $main_alliance->alliance_name");
		print("<table cellspacing=\"0\" cellpadding=\"5\" border=\"0\" class=\"standard\"><tr><th align=center>Alliance Name</th>");
		print("<th align=\"center\">Amount</th></tr>");
		while ($db->next_record()) {
			
			$kills = $db->f("kills");
			$id = $db->f("alliance_id_2");
			if ($id > 0) {
				
				$killer_alliance = new SMR_ALLIANCE($id, SmrSession::$game_id);
				$alliance_name = $killer_alliance->alliance_name;
			
			} elseif ($id == 0) $alliance_name = "<font color=\"blue\">No Alliance</font>";
			elseif ($id == -1) $alliance_name = "<font color=\"blue\">Forces</font>";
			elseif ($id == -2) $alliance_name = "<font color=\"blue\">Planets</font>";
			elseif ($id == -3) $alliance_name = "<font color=\"blue\">Ports</font>";
			
			print("<tr><td align=\"center\">$alliance_name</td><td align=\"center\">$kills</td></tr>");
			
		}
		print("</table>");
		
	} else print("$main_alliance->alliance_name has no kills!");
	print("</td><td width=\"10%\">&nbsp;</td><td width=\"45%\" align=\"center\" valign=\"top\">");
	$db->query("SELECT * FROM alliance_vs_alliance WHERE alliance_id_2 = $var[alliance_id] " .
				"AND game_id = $player->game_id ORDER BY kills DESC");
	if ($db->nf() > 0) {
		
		print("<div align=\"center\">Deaths for $main_alliance->alliance_name");
		print("<table cellspacing=\"0\" cellpadding=\"5\" border=\"0\" class=\"standard\"><tr><th align=center>Alliance Name</th>");
		print("<th align=\"center\">Amount</th></tr>");
		while ($db->next_record()) {
			
			$kills = $db->f("kills");
			$id = $db->f("alliance_id_1");
			if ($id > 0) {
				
				$killer_alliance = new SMR_ALLIANCE($id, SmrSession::$game_id);
				$alliance_name = $killer_alliance->alliance_name;
			
			} elseif ($id == 0) $alliance_name = "<font color=\"blue\">No Alliance</font>";
			elseif ($id == -1) $alliance_name = "<font color=\"blue\">Forces</font>";
			elseif ($id == -2) $alliance_name = "<font color=\"blue\">Planets</font>";
			elseif ($id == -3) $alliance_name = "<font color=\"blue\">Ports</font>";
			
			print("<tr><td align=\"center\">$alliance_name</td><td align=\"center\">$kills</td></tr>");
			
		}
		print("</table>");
		
	} else print("$main_alliance->alliance_name has no deaths!");
	print("</td></tr></table>");
}		

?>