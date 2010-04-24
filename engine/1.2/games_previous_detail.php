<?php
require_once(get_file_loc("smr_history_db.inc"));
$game_name = $var['game_name'];
$game_id = $var['game_id'];
print_topic("$game_name - Extended Stats");

if (isset($_REQUEST['action'])) $action = $_REQUEST['action'];
print("<div align=center>");
if (empty($action)) {

	print("Click a link to view those stats.<br><br>");
	$container = array();
	$container["url"] = "skeleton.php";
	$container["body"] = "games_previous.php";
	$container["game_id"] = $game_id;
	$container["game_name"] = $game_name;
	print_link($container, "<b>Basic Game Stats</b>");
	print("<br>");
	$container["body"] = "games_previous_detail.php";
	$container["game_id"] = $game_id;
	$container["game_name"] = $game_name;
	print_form($container);
	print_submit("Top Mined Sectors");
	print("<br>");
	print_submit("Sectors with most Forces");
	print("<br>");
	print_submit("Top Killing Sectors");
	print("<br>");
	print_submit("Top Planets");
	print("<br>");
	print_submit("Top Alliance Experience");
	print("<br>");
	print_submit("Top Alliance Kills");
	print("<br>");
	print_submit("Top Alliance Deaths");
	print("</form>");
	print("<br>");

} else {

	if ($action == "Top Mined Sectors") { $sql = "mines"; $from = "sector"; $dis = "Mines"; }
	elseif ($action == "Sectors with most Forces") { $sql = "mines + combat + scouts"; $from = "sector"; $dis = "Forces"; }
	elseif ($action == "Top Killing Sectors") { $sql = "kills"; $from = "sector"; $dis = "Kills"; }
	elseif ($action == "Top Planets") { $sql = "(turrets + hangers + generators) / 3"; $from = "planet"; $dis = "Planet Level"; }
	elseif ($action == "Top Alliance Experience") { $sql = "SUM(experience)"; $from = "player"; $dis = "Alliance Experience"; $gr = "dummy";}
	elseif ($action == "Top Alliance Kills") { $sql = "kills"; $from = "alliance"; $dis = "Alliance Kills"; $gr = "dummy";}
	elseif ($action == "Top Alliance Deaths") { $sql = "deaths"; $from = "alliance"; $dis = "Alliance Deaths"; $gr = "dummy";}

	$db2 = new SMR_HISTORY_DB();
	if (empty($gr)) {
		
		$db2->query("SELECT $sql as val, sector_id FROM $from WHERE game_id = $game_id $gr ORDER BY $sql DESC LIMIT 30");
	
		$container = array();
		$container["url"] = "skeleton.php";
		$container["body"] = "games_previous_detail.php";
		$container["game_id"] = $game_id;
		$container["game_name"] = $game_name;
		print_link($container, "<b>&lt;&lt;Back</b>");
		print_table();
		print("<tr><th align=center>Sector ID</th><th align=center>$dis</th></tr>");
		while ($db2->next_record()) {
	
			$sector_id = $db2->f("sector_id");
			$val = $db2->f("val");
			print("<tr><td>$sector_id</td><td>$val</td></tr>");
	
		}
		print("</table>");
		
	} else {
		$sql = "SELECT alliance_id, $sql as val FROM $from WHERE game_id = $game_id AND alliance_id > 0 GROUP BY alliance_id ORDER BY val DESC LIMIT 30";
		$db2->query("$sql");
		$db = new SMR_HISTORY_DB();
		$container = array();
		$container["url"] = "skeleton.php";
		$container["body"] = "games_previous_detail.php";
		$container["game_id"] = $game_id;
		$container["game_name"] = $game_name;
		print_link($container, "<b>&lt;&lt;Back</b>");
		print_table();
		print("<tr><th align=center>Alliance ID</th><th align=center>$dis</th></tr>");
		while ($db2->next_record()) {
	
			$alliance_id = $db2->f("alliance_id");
			$db->query("SELECT * FROM alliance WHERE alliance_id = $alliance_id AND game_id = $game_id");
			$db->next_record();
			$name = stripslashes($db->f("alliance_name"));
			$val = $db2->f("val");
			print("<tr><td>");
			$container = array();
			$container["url"] = "skeleton.php";
			$container["body"] = "alliance_detail_old.php";
			$container["game_id"] = $game_id;
			$container["alliance_id"] = $alliance_id;
			print_link($container, $name);
			print("</td><td>$val</td></tr>");
	
		}
		print("</table>");
	}

}
print("</div>");
$db = new SmrMySqlDatabase();

?>