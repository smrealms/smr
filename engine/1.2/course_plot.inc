<?php
require_once(get_file_loc('smr_sector.inc'));
		$sector = new SMR_SECTOR($player->sector_id, SmrSession::$game_id, SmrSession::$old_account_id);

// used further down
define ('TIMEOUT_TO_UPDATE', 300);
define ('TIMEOUT_TO_DELETE',7200);

// Course_Plotter class.
// Usage:
//	Initialise the course with set_course (start,target,game_id)
//	Use plot() to process the plot
//	$plotted_course[0] is the distance, $plotted_course[1] is an array of sectors describing the route
// NOTE: This generates distance AND the course. For distance only plots use the Distance_Plotter class in distance_plot.inc
class Course_Plotter extends SmrMySqlDatabase{
	// Public data

	// Container for the resultant plotted course
	// Format: plotted_course = ( Length of plot, Sectors in plot (...) )
	var $plotted_course;
	
	// The generated course goes from $start -> $target
	var $start;
	var $target;

	// The game the user is currently playing
	var $game_id;

	// Private data

	// Galaxy ids for the two nodes on the course that vary per user
	var $start_galaxy;
	var $target_galaxy;

	// Container to cache galaxy information and avoid hitting the DB too many times
	// Format: galaxies = [galaxy_id] => (Min sector, Max sector, Total Sectors)
	var $galaxies;

	// Container to store a local version of a galaxy map to prevent hitting the DB for every link plot
	// Format:
	var $galaxy_map;

	// The galaxy_id of the currently stored galaxy map
	var $galaxy_map_id;

	// Container to describe the node network
	// Format:
	var $network_map;

	// Array of links between nodes
	// Start, End, Length, raw route string, array of sectors in the route.
	// NOTE: Start sector_id is ALWAYS greater than End sector_id
	var $links;

	// Indicate whether we have UPDATED any cached result, new entries don't count
	var $cache_updated;

	// This gets used to avoid the time() function wherever possible
	var $current_time;

	// Public Functions

	// Constructor
	function Course_Plotter() {
		$this->links = array();
		$this->galaxies = array();
		$this->galaxy_map_id = -1;
		$this->galaxy_map = array();
		$this->network_map = array();
		$this->plotted_course = array();
		// Make sure the constructor in the DB class is called to setup the DB connection
		$this->SmrMySqlDatabase();
	}

	// Desctructor (PHP doesn't implement desctuctor natively so you'll have to remember to call it in the code that uses this object).
	function Course_Plotter_Destructor() {
		if($this->cache_updated==true) {
			// Clear any old plots from the cache table (To stop it getting too large).
			// To avoid doing this every time we only delete if a cache entry was updated.
			$this->query('DELETE FROM plot_cache WHERE timeout < '  . (time() - TIMEOUT_TO_DELETE));
		}
	}

	function set_course($start,$target,$game_id) {
		$this->start = $start;
		$this->target = $target;
		$this->game_id = $game_id;

		// Reset the internal arrays for a new plot
		// FIXME: We should be able to keep more information between plots. Right now it's only safe to keep galaxy_map and galaxies
		$this->links = array();
		$this->network_map = array();
		$this->plotted_course = array();
		$this->cache_updated = false;
		$this->current_time = time();
	}

	function plot() {

		$start = $this->start;
		$target = $this->target;
		$links = &$this->links;
		$plotted_course = &$this->plotted_course;

		// perform some basic checks on both numbers
		if (empty($start) || empty($target))
			create_error('Where do you want to go today?');

		if (!is_numeric($start) || !is_numeric($target))
			create_error('Please enter only numbers!');
	
		if ($start == $target)
			create_error('Hmmmm...if ' . $start . '=' . $target . ' then that means...YOU\'RE ALREADY THERE! *cough*you\'re real smart*cough*');

		// get the galaxy_ids of the sectors that the user has requested a plot between
		$this->start_galaxy = $this->get_galaxy_id($start);
		$this->target_galaxy = $this->get_galaxy_id($target);

		// The route table contains four distinct elements.
		// 1. Routes from $start -> warps in the galaxy containing $start
		// 2. Routes from $target -> warps in the galaxy containing $target
		// 3. Routes from warp -> warp that are INTERNAL to each galaxy
		// 4. Routes from warp -> warp that CONNECT each galaxy
		// The final table describes a network of routes that is used to determine the shortest path between $start and $target
		// (3) and (4) are fixed by galaxy design. (1) and (2) are variable depending on the plot being generated.

		// First we check for the trivial case where both sectors are in the same galaxy
		if ($this->start_galaxy == $this->target_galaxy) {

			// Only one route is required
			$this->build_link($start, $target, $this->start_galaxy) ;

			$plotted_course[1][0] = $start;

			if($start>$target){
				$plotted_course[1] = array_merge($plotted_course[1], array_reverse($this->links[0][3]));
			}
			else	{
				$plotted_course[1] = array_merge($plotted_course[1], $this->links[0][3]);
			}
			$plotted_course[1][] = $target;
			$plotted_course[0] = $this->links[0][2];
		}
		else{

			// We need to work from the full network map
			$this->build_network_map();
			$this->process_network_map();
		}
	}

	// Private Functions

	function get_galaxy_id($sector_id) {

		$galaxies = &$this->galaxies;

		// We keep galaxy information in a local array.
		// This function gets used a lot, so hitting the DB every time would be horribly inefficient
		$galaxy_ids  = array_keys($galaxies);
		$num_galaxies = count($galaxy_ids);
		for ($i=0;$i<$num_galaxies;$i++) {
			if($galaxies[$galaxy_ids[$i]][0] <= $sector_id && $galaxies[$galaxy_ids[$i]][1] >= $sector_id) {
				$galaxy_id = $galaxy_ids[$i];
				return $galaxy_id;
			}
		}

		// No locally cached entry in the galaxy information array so make one.
		$this->query('SELECT galaxy_id FROM sector WHERE
					 sector_id = ' .  $sector_id  .  ' AND
					 game_id = '  .  $this->game_id  .  ' LIMIT 1');

		if($this->next_record()) {
			$galaxy_id = $this->f('galaxy_id');
		} else
			create_error('The sector #' . $sector_id . ' doesn\'t exist.');

		// Retrieve the information for the local galaxies array
		$this->query('SELECT MIN(sector_id), MAX(sector_id) FROM sector WHERE
					 galaxy_id = '  .  $galaxy_id .  ' AND
					 game_id = '  .  $this->game_id);

		if ($this->next_record()) {

			// Store an entry in the galaxies array
			$galaxies[$galaxy_id] = array(
				$this->f('MIN(sector_id)'),
				$this->f('MAX(sector_id)'),
				$this->f('MAX(sector_id)') - $this->f('MIN(sector_id)') + 1
			);

		} else
			create_error('Couldn\'t get the MIN and MAX sectors for a galaxy!');	

		return $galaxy_id;

	}

	function build_galaxy_map($galaxy_id) {
		$galaxy_map = &$this->galaxy_map;

		// Clean out any old galaxy_map
		$galaxy_map = array();

		// First things first, get every sector in the galaxy from the DB
		$this->query('SELECT sector_id, link_up, link_down, link_left, link_right FROM sector WHERE
					 galaxy_id = '  .  $galaxy_id  .  ' AND
					 game_id = '  .  $this->game_id  .  ' LIMIT ' . $this->galaxies[$galaxy_id][2]);

		// Array is like this. $galaxy_map[sector_id] = link_up, link_down, link_left, link_right, visitor
		// Where visitor is 0:unvisited, 1: start group, 2: target group, 3:avoid
		while ($this->next_record()) {
			$galaxy_map[$this->f('sector_id')] = array(
				$this->f('link_up'),
				$this->f('link_down'),
				$this->f('link_left'),
				$this->f('link_right'),
				0
			);
		}

		// Maps can get quite large, release the result set immediately
		$this->free();

	}

	// Builds a complete route entry and enters appropriate nodes in the network map based on the $start, $target, $galaxy_id
	function build_link ($start, $target, $galaxy_id) {

		$links = &$this->links;
		$galaxies = &$this->galaxies;
		$galaxy_map = &$this->galaxy_map;
		$network_map = &$this->network_map;

		// $i is fixed to be an index to the next entry in routes_in
		$i = count($links);

		// Determine low/high sense of $start and $target
		if ($start > $target) {

			$sector_id_1 = $target;
			$sector_id_2 = $start;

		} else {

			$sector_id_1 = $start;
			$sector_id_2 = $target;

		}

		// Build our single route
		$links[$i] = array( $sector_id_1,$sector_id_2,0,array());

		// Check to see if this route is in the plotted course cache
		$this->query('SELECT route, length, timeout FROM plot_cache WHERE
					 sector_id_1 = ' . $sector_id_1 . ' AND
					 sector_id_2 = ' . $sector_id_2 . ' AND
					 game_id = ' . $this->game_id . ' LIMIT 1');

		if ($this->next_record()) {
			
			// We found a cached entry
			$links[$i][2] = $this->f('length');				// Store the length of the route
			$links[$i][3] = unserialize($this->f('route'));		// Store the raw route information

			// To optimise use of mysql's cache (Not the the plot_cache table) the timeout doesn't get updated on every access
			if ( $this->f('timeout') < $this->current_time  - TIMEOUT_TO_UPDATE) {
				$this->cache_updated = true;
				$this->query('UPDATE plot_cache SET timeout = ' . $this->current_time . ' WHERE
							 sector_id_1 = ' . $sector_id_1 . ' AND
							 sector_id_2 = ' . $sector_id_2 . ' AND
							 game_id = ' . $this->game_id . ' LIMIT 1');

			}

		}
		else {
			// We only want to build a galaxy map if it hasn't already been stored
			if($galaxy_id != $this->galaxy_map_id) {
				// Build the galaxy map
				// Fill in the galaxy map array with the appropriate galaxy
				$this->build_galaxy_map($galaxy_id);
				$this->galaxy_map_id= $galaxy_id;

			}
			else {

				// Reset the galaxy map
				$galaxy_map_keys = array_keys($galaxy_map);
				$num_galaxy_map_keys = count($galaxy_map_keys);
				for($j=0;$j<$num_galaxy_map_keys;$j++) {
					$galaxy_map[$galaxy_map_keys[$j]][4] = 0;
				}
				unset($galaxy_map_keys);
	
			}

			// Get the plotted course
			$this->process_link($i);

			// Store the new plot in the cache
			// This will give an error if two people try to plot exactly the same course at exactly the same time
			// it can be safely ignored. The error will be for duplicate keys, the second insert will fail, which is ok.
			$this->query('REPLACE INTO plot_cache (game_id, sector_id_1, sector_id_2, length, timeout, route) VALUES (
						' . $this->game_id . ',' . $links[$i][0] . ',' . $links[$i][1]. ',' . $links[$i][2] . ',' . $this->current_time . ',\'' . serialize($links[$i][3]) . '\')');

		}

		// Add the links to the appropriate node in the network map
		$j = count($network_map[$sector_id_1]);
		$network_map[$sector_id_1][count($network_map[$sector_id_1])] = $i;
		$network_map[$sector_id_2][count($network_map[$sector_id_2])] = $i;

	}

	function process_link($link_id) {
		$links = &$this->links;
		$galaxy_map = &$this->galaxy_map;

		// Ok, now build an array of wavefront points which I call runners
		// We start out with two, one at the start sector the other at the target sector
		// Array is like this: $runners[runner_group][$sector_id] = array of sectors in path
		// runner_group - 0:Runner began at start, 1:Runner began at end
		// The algorithm now has 2 wavefronts, not 1
		$runners = array();

		$runners[0][$links[$link_id][0]] = array();
		$runners[1][$links[$link_id][1]] = array();

		// Now set the start and target sectors as having been visited
		$galaxy_map[$links[$link_id][0]][4] = 1;
		$galaxy_map[$links[$link_id][1]][4] = 2;

		// This tracks how many iterations have passed and therefore how long the route is
		$length = 0;

		// Ok, now run until such times as the target sector is found (This "should" never result in an infinite loop)
		while (true) {

			$length++;

			// Contains information of new runners
			$new_runners = array();

			// Remember: k = runner group (start/target), i = runner id(sector)
			for ($k = 0; $k<2; $k++) {
				// Here to prevent recalculating on every iteration
				$total_runners = sizeof($runners[$k]);

				// Keys are non-sequential, a foreach loop would make a copy of the array which would be BAD
				// Therefore the simplest thing to do here is just pull out the keys from runners into a separate array
				$runners_keys = array_keys($runners[$k]);

				// Ok, let the runner run
				for ($i = 0; $i < $total_runners; $i++) {

					// Keep track of the sector this particular runner is in
					$runner_sector = $runners_keys[$i];

					// Now create the new runners if possible
					for($j = 0; $j < 4; $j++) {

						// The sector being moved to
						$sector_id = $galaxy_map[$runner_sector][$j];

						if ($sector_id > 0 && $galaxy_map[$sector_id][4] == 0) {
						
							$new_runners[$k][$sector_id] = $runners[$k][$runner_sector];
							$new_runners[$k][$sector_id][] = $sector_id;

							// Set sector as having been visited by the appropriate visitor type
							$galaxy_map[$sector_id][4] = $k + 1;
						}
						else if ($sector_id > 0 && $galaxy_map[$sector_id][4] == 2 - $k ) {

							// This means we've just hit a sector already visited by the opposing set of runners
							// ie the two wavefronts have met so we have the shortest route available
							// The complimentary half of the route exists in one of two places
							// runner group 1 will find it in $runners
							// runner group 2 will find it in $new_runners since runner group 1 has already run
							if ($k == 0) {
								$links[$link_id][3] = array_merge($runners[0][$runner_sector],array_reverse($runners[1][$sector_id]));
								$links[$link_id][2] = $length*2 - 1;
							}
							else {
								$links[$link_id][3] = array_merge ($new_runners[0][$sector_id],array_reverse($runners[1][$runner_sector]));
								$links[$link_id][2] = $length*2;
							}

							unset ($runners, $new_runners, $runners_keys);
							return;
						}
					}
				}
			}

			// The course hasn't been found.
			// No new runners means we've failed
			if(!isset($new_runners[0]) || !isset($new_runners[1])) 
				create_error('Link processing failure!');

			// Setup for the next iteration.
			unset ($runners);
			$runners = $new_runners;
			unset ($new_runners);

		}
	}

	function build_network_map() {

		$links = &$this->links;
		$galaxies = &$this->galaxies;
		$galaxy_map = &$this->galaxy_map;
		$network_map = &$this->network_map;

		// First grab all the warps
		$this->query('SELECT sector_id_1, sector_id_2 FROM warp WHERE game_id = '  .  $this->game_id);


		// Array of nodes for network (Same principle as the galaxy_map for routes) to be used calculating a path through the network
		// Array is like this. $network_map[$sector_id] = links($route_id)
		// Where visited is 0:unvisited 1:visited for every node

		// Ok, now we build the warp -> warp CONNECTION routes
        	while ($this->next_record()) {
			// We do two things here.
			// 1. We create the CONNECTION route
			// 2. We create a list of warp sectors and their galaxy ids

			// Determine low/high sense of $start and $target
			if ($this->f('sector_id_1') >  $this->f('sector_id_2')) {

				$sector_id_1 = $this->f('sector_id_2');
				$sector_id_2 = $this->f('sector_id_1');

			} else {

				$sector_id_1 = $this->f('sector_id_1');
				$sector_id_2 = $this->f('sector_id_2');

			}

			// Build the connector route
			$links[] = array($sector_id_1,$sector_id_2,5,array());

			// We need 2 entries in $network_map (One for each sector)
			$i = count($links)-1;
			$network_map[$sector_id_1][count($network_map[$sector_id_1])] = $i;
			$network_map[$sector_id_2][count($network_map[$sector_id_2])] = $i;

		}

		// Free results
		$this->free();

		// Array of sectors containing warps to reduce calls to get_galaxy_id
		// Array format is warp_sectors[sector_id] = galaxy_id
		$warp_sectors = array();

		// Pull out the sector_ids of all the warps
		$warp_keys= array_keys($network_map);
		sort($warp_keys, SORT_NUMERIC);

		// Ok, now build the warp->warp routes within each galaxy
		// A route is built from the start warp to each warp with a higher sector value that is in the same galaxy
		$num_warps = count($warp_keys);

		// Setup the warp_sectors entry for the first warp
		$warp_sectors[$warp_keys[0]] = $this->get_galaxy_id($warp_keys[0]);

		for($i=0;$i<$num_warps;$i++) {

			// We take this opportunity to also build the $start -> warp and $target -> warp routes
			if($this->start_galaxy == $warp_sectors[$warp_keys[$i]]) {
				// Warp is in the same galaxy as the start sector, build $start -> warp route;
				$this->build_link($this->start, $warp_keys[$i], $this->start_galaxy) ;
			}
			else if($this->target_galaxy == $warp_sectors[$warp_keys[$i]]) {
				// Warp is in the same galaxy as the target sector, build $target -> warp route;
				$this->build_link($this->target, $warp_keys[$i], $this->target_galaxy) ;
			}

			for($j = $i +1;$j<$num_warps;$j++) {

				// Fill in any unset warp_sectors entries
				if(!isset($warp_sectors[$warp_keys[$j]])) {
					$warp_sectors[$warp_keys[$j]] = $this->get_galaxy_id($warp_keys[$j]);
				}

				if($warp_sectors[$warp_keys[$i]] == $warp_sectors[$warp_keys[$j]]) {
					$this->build_link($warp_keys[$i], $warp_keys[$j], $warp_sectors[$warp_keys[$i]]) ;
				}
				else {
					break;
				}
			}
		}

		// We don't need these any more
		unset($warp_sectors, $warp_keys);
	}

	function process_network_map () {

		$links = &$this->links;
		$galaxies = &$this->galaxies;
		$galaxy_map = &$this->galaxy_map;
		$network_map = &$this->network_map;
		$plotted_course = &$this->plotted_course;

		// Now this is where things get interesting. 
		// There are multiple possible paths through the network, and the links on the network are all a different length.
		// So in order to get the true shortest path we need to map ALL possible routes from $start->$target
		// We'll do a comparison of start->warp + warp->warp + warp->target to determine the true shortest path

		$num_nodes = count($network_map);

		// This follows the same principle as the route code. A runner is set running, it creates new runners at every node it reaches and can never go backwards.
		// The major differences are that a new runner inherits only the node history of its parent, not all the runners.
		// Also if a runner hits the target it terminates, but the rest of the runners continue until no active runners remain.
	
		// Build an array of runners. Array format is current_node, current_length, raw route, node_history(...) where the first node in history is the current node
		// WARNING: There should never be a sector 0 in the galaxy maps or this will screw up royally

		// We store the least length path as node_path, and it's length as node_path_length
		$node_path_length = 9999;

		// We begin with one runner at $start
		$runners[] = array($this->start,0, array($this->start), array());

		// We'll break out of this infinite loop
		// WARNING: While testing keep a variable to break the loop after a few iterations
		while(true) {
			$num_runners = count($runners);

			$new_runners = array();

			// Go through each runner in turn
			for($i=0;$i<$num_runners;$i++) {

				// Any runners with a total length over the best case length currently found are not processed
				if($runners[$i][1] < $node_path_length){

					$current_node = $runners[$i][0];
					// Mark the current node as being visited in the node history
					$runners[$i][3][$current_node] = TRUE;

					// Have we made it to the target sector?
					if($current_node == $this->target) {
						// Store the node path. The runner has done it's job, it dies without spawning any children
						$node_path_length = $runners[$i][1];
						$node_path = $runners[$i][2];
					}
					else {

						// Get the link information from $network_map
						$num_links = count($network_map[$current_node]);

						// Ok. Each of these links is an index into the $links array
						// We start a new runner at the node pointed to by the link

						for($j=0;$j<$num_links;$j++){
							if($links[$network_map[$current_node][$j]][0] == $current_node){
								$next_node = $links[$network_map[$current_node][$j]][1] ;
							}
							else{
								$next_node = $links[$network_map[$current_node][$j]][0] ;
							}
							$link_length = $links[$network_map[$current_node][$j]][2];
							$current_node_path = $runners[$i][2];
							// There is very little point in creating child runners if its path would exceeded the current minimal node_path_length
							if(!isset ( $runners[$i][3][$next_node] ) && ($runners[$i][1] + $link_length) < $node_path_length ) {
								$current_node_path[] = $next_node;
								$new_runners[] = array($next_node, $runners[$i][1] + $link_length,
									$current_node_path , $runners[$i][3]);

								// Logic dicates that the path 'current_node->next_node->node connected to current_node' will be longer than the path
								// current_node->node connected to current_node. So we can mark all the nodes connected to current node as visited
								// This massively reduces the rumber of runners
		 						for($k=0;$k<$num_links;$k++){
									if($links[$network_map[$current_node][$k]][0] == $current_node){
										$mark_node = $links[$network_map[$current_node][$k]][1] ;
									}
									else{
										$mark_node = $links[$network_map[$current_node][$k]][0] ;
									}
									if ($mark_node != $next_node) {
										$new_runners[count($new_runners)-1][3][$mark_node] = TRUE;
									}
								}				
							}
						}
					}
				}
			}
	
			// Ok, route hasn't been found, so setup again, this looks odd but is totally deliberate
			unset ($runners);
			$runners = $new_runners;
			unset ($new_runners);
	
			if(count($runners) == 0)
				break;
		}

		// If we're still stuck at the default length then something has gone horribly wrong
		if($node_path_length == 9999)
			create_error('Failure in node path generation!');

		// Finally we turn our minimal node path into a complete route
		$num_nodes = count($node_path);

		for($i=1;$i<$num_nodes;$i++) {
		// Add the node's sector_id to the plotted course
			$plotted_course[1][] = $node_path[$i-1];

			// All routes are stored from low sector to high sector
			// Determine low/high sense of $start and $target
			if ($node_path[$i-1] > $node_path[$i]) {

				$sector_id_1 = $node_path[$i];
				$sector_id_2 = $node_path[$i-1];
				$reverse = true;

			} else {

				$sector_id_1 = $node_path[$i-1];
				$sector_id_2 = $node_path[$i];
				$reverse = false;

			}

			// Get the route using the universe map
			$num_links = count($network_map[$sector_id_1]);

			// Ok. Each of these links is an index into the $links array
			// We start a new runner at the node pointed to by the link

			for($j=0;$j<$num_links;$j++){
				if($links[$network_map[$sector_id_1][$j]][1] == $sector_id_2){
					if($reverse == true) {
						$plotted_course[1] = array_merge($plotted_course[1],array_reverse($links[$network_map[$sector_id_1][$j]][3]));
					}
					else {
						$plotted_course[1] = array_merge($plotted_course[1],$links[$network_map[$sector_id_1][$j]][3]);
					}
					break;
				}
			}
		}

		// Add the target sector of the whole course
		$plotted_course[1][] = $this->target;
		$plotted_course[0] = $node_path_length;

	}
}

?>
