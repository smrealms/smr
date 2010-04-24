<?php
require_once(get_file_loc('smr_sector.inc'));
		$sector = new SMR_SECTOR($player->sector_id, SmrSession::$game_id, SmrSession::$old_account_id);

print_topic("PLOT A COURSE");

// create menu
$container = array();
$container['url'] = 'skeleton.php';
$container['body'] = 'course_plot.php';
$menue_items[] = create_link($container, 'Plot a Course');
if($player->land_on_planet == 'FALSE') {
	$container['body'] = 'map_local.php';
	$menue_items[] = create_link($container, 'Local Map');
}
$menue_items[] = '<a href="' . URL . '/map_galaxy.php" target="_blank">Galaxy Map</a>';

// print it
print_menue($menue_items);

echo '<table cellspacing="0" cellpadding="0" style="width:100%;border:none"><tr><td style="padding:0px;vertical-align:top">';
print("The plotted course is " . $var["distance"] . " sectors long.");
echo '</td><td style="padding:0px;vertical-align:top;width:32em">';

// get the array back
$route = unserialize($var["plotted_course"]);

$full = (implode(' - ', $route));

// throw start sector away
// it's useless for the route
array_shift($route);

// now get the sector we are going to but don't remove it (sector_move_processing does it)
$next_sector = $route[0];

if ($next_sector == $sector->link_up ||
	$next_sector == $sector->link_down ||
	$next_sector == $sector->link_left ||
	$next_sector == $sector->link_right ||
	$next_sector == $sector->warp) {

	// save this to db (if we still have something
	if (!empty($route)) {

		$db->query("REPLACE INTO player_plotted_course
					(account_id, game_id, distance, course)
					VALUES($player->account_id, $player->game_id, " . $var["distance"] . ", '" . serialize($route) . "')");

	}

	//print_form($container);
	if ($player->land_on_planet == "FALSE") {
		$container = array();
		$container["url"] = "sector_move_processing.php";
		$container["target_page"] = "current_sector.php";
		$container["target_sector"] = $next_sector;

		print_button($container, "Follow plotted course - " .  $next_sector . " (" . $var["distance"] . ")");
	
		if (!empty($ship->hardware[HARDWARE_SCANNER])) {
	
			print("&nbsp;&nbsp;&nbsp;");
			$container = array();
			$container["url"]			= "skeleton.php";
			$container["body"]			= "sector_scan.php";
			$container["target_sector"] = $next_sector;
			print_button($container, "Scan");
	
		}
	}
}

echo '</td></tr></table><br><h2>Plotted Course</h2><br>';
echo $full;

?>