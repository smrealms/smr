<?php
require_once(get_file_loc('smr_sector.inc'));
		$sector = new SMR_SECTOR($player->sector_id, SmrSession::$game_id, SmrSession::$old_account_id);

// include helper funtions
include("course_plot_new.inc");

// used further down
$timeout_to_update = 300;
$timeout_to_delete = 7200;

if (isset($var["from"])) $start = $var["from"];
else $start = $_POST["from"];
if (isset($var["to"])) $target = $var["to"];
else $target = $_POST["to"];

if (get_galaxy_id($start, $player->game_id) == get_galaxy_id($target, $player->game_id)) {
	
	//we want the fast plot
	$container = array();
	$container["url"] = "course_plot_processing.php";
	$container["from"] = $start;
	$container["to"] = $target;
	forward($container);
	
}
// perform some basic checks on both numbers
if (empty($start) || empty($target))
	create_error("Where do you want to go today?");

if (!is_numeric($start) || !is_numeric($target))
	create_error("Please enter only numbers!");
	
if ($start == $target)
	create_error("Hmmmm...if $start = $target then that means...YOU'RE ALREADY THERE! *cough*you're real smart*cough*");

$account->log(5, "Player plots to $target.", $player->sector_id);

// Determine low/high sense of $start and $target and therefore route direction required
if ($start > $target) {

	$sector_id_1 = $target;
	$sector_id_2 = $start;
	$reverse = true;

} else {

	$sector_id_1 = $start;
	$sector_id_2 = $target;
	$reverse = false;

}
// Array of routes, values mean:
// Start, End, Length, Galaxy, Min sector in galaxy, Max sector in galaxy, Total sectors in galaxy, raw route string.
// initialize it
$routes = array("START" => $sector_id_1,"END" => $sector_id_2,"LEN" => 0,"GAL" => 0,"RAW" => '');

// Check the cached results table
// NOTE: I could do some fairly funky stuff with regexp on the raw routes to pull out even partial
// plots from the cache, however I'm not very confident that it would actually turn out faster than
// figuring out the routes. Something for you guys to play with later if you get bored :>
$db->query("SELECT route, length, timeout
			FROM plot_cache
			WHERE sector_id_1 = $sector_id_1 AND
				  sector_id_2 = $sector_id_2 AND
				  game_id = $player->game_id
			LIMIT 1");

if ($db->next_record()) {

	$routes["LEN"]	= $db->f("length");		// Store the length of the route
	$routes["RAW"]	= $db->f("route");		// Store the raw route information
	$cached				= true;					// Set the route as having come from the cache
	$cache_timeout		= $db->f("timeout");	// Store the timeout for that information

} else
	$cached				= false;				// Set the route as not having come from the cache

// Only run through this part if no result is found in the cache.
if ($cached == false) {

	// get the galaxy_ids of the sectors that the user has requested a plot between
	$galaxy_id_1 = get_galaxy_id($sector_id_1, $player->game_id);
	if (!$galaxy_id_1)
   		create_error("The sector #$sector_id_1 doesn't exist");

	// Store galaxy_id for route 1
	$routes["GAL"] = $galaxy_id_1;

		
	// Get route 1 (Note that this is passed by reference so get_plot can change it)
	if(!get_plot($routes, $player->game_id))
		create_error("Not able to plot the course!");

}

// get results
$route = explode(":", $routes["RAW"]);

// Bet you wondered when I'd use this :>
if ($reverse == true)
	$route = array_reverse($route);

// Figured I'd throw this one in for free
$distance = $routes["LEN"];


// This is to maintain the plot_cache table.
// Old plots get their timeout updated every 5 minutes, not every time they are used. This is because constantly
// changing them would drive them out of the mysql cache, which isn't what we want for speed purposes.
// Final action here is to clear out any plots that have expired to stop the table getting huge. It's supposed to
// be small and fast to speed up repetitive plots, not maintain a full plot map for the entire game (Which would be
// slower to search on than just calculating the things). 10 minutes seemed like a reasonable amount to have here, enough
// to hit a planet a couple of times and head to UNO without the plot falling out of the cache. Remember that traders will
// be using the stored plot a lot, and therefore it will keep refreshing its timeout so 10 minutes is plenty.

if ($cached == True) {

	// To optimise use of mysql's cache (Not the the plot_cache table) the timeout only gets updated every 5 minutes
	if (time() - $cache_timeout < $timeout_to_update) {

		$db->query("UPDATE plot_cache
					SET timeout = " . time() . "
					WHERE sector_id_1 = $sector_id_1 AND
						  sector_id_2 = $sector_id_2 AND
						  game_id = $player->game_id");

	}

} else {

	// Dump the new plot into the DB
	// This will give an error if two people try to plot exactly the same course at exactly the same time
	// it can be safely ignored. The error will be for duplicate keys, the second insert will fail, which is ok.
	$db->query("REPLACE INTO plot_cache
				(game_id, sector_id_1, sector_id_2, length, timeout, route)
				VALUES($player->game_id, $sector_id_1, $sector_id_2, $distance, " . time() . ", '" . $routes["RAW"] . "')");

}

// Tidy up, I don't know how much memory the rest of the script needs
unset ($routes);

// Clear any old plots from the cache table (To stop it getting too large), 10 minutes to timeout seems reasonable
$db->query("DELETE FROM plot_cache WHERE timeout < " . (time() - $timeout_to_delete));

$container = array();
$container["url"] = "skeleton.php";
$container["body"] = "course_plot_result.php";
$container["plotted_course"] = serialize($route);
$container["distance"] = $distance;

forward($container);

?>