<?
require_once(get_file_loc('SmrSector.class.inc'));
$sector =& SmrSector::getSector(SmrSession::$game_id, $player->getSectorID(), SmrSession::$account_id);

$smarty->assign('PageTopic','PLOT A COURSE');

// create menu
$container = array();
$container['url'] = 'skeleton.php';
$container['body'] = 'course_plot.php';
$menue_items[] = create_link($container, 'Plot a Course');
if(!$player->isLandedOnPlanet()) {
	$container['body'] = 'map_local.php';
	$menue_items[] = create_link($container, 'Local Map');
}
$menue_items[] = '<a href="' . $URL . '/map_galaxy.php" target="_blank">Galaxy Map</a>';

// echo it
$PHP_OUTPUT.= create_menue($menue_items);

$PHP_OUTPUT.= '<table cellspacing="0" cellpadding="0" style="width:100%;border:none"><tr><td style="padding:0px;vertical-align:top">';
$PHP_OUTPUT.=('The plotted course is ' . $var['distance'] . ' sectors long.');
$PHP_OUTPUT.= '</td><td style="padding:0px;vertical-align:top;width:32em">';

// get the array back
$route = unserialize($var['plotted_course']);

$full = (implode(' - ', $route));

// throw start sector away
// it's useless for the route
array_shift($route);

// now get the sector we are going to but don't remove it (sector_move_processing does it)
$next_sector = $route[0];

if ($next_sector == $sector->getLinkUp() ||
	$next_sector == $sector->getLinkDown() ||
	$next_sector == $sector->getLinkLeft() ||
	$next_sector == $sector->getLinkRight() ||
	$next_sector == $sector->getLinkWarp()) {

	// save this to db (if we still have something
	if (!empty($route)) {

		$db->query('REPLACE INTO player_plotted_course
					(account_id, game_id, distance, course)
					VALUES('.$player->getAccountID().', '.$player->getGameID().', ' . $var['distance'] . ', ' . $db->escape_string(serialize($route)) . ')');

	}

	//$PHP_OUTPUT.=create_echo_form($container);
	if (!$player->isLandedOnPlanet()) {
		$container = array();
		$container['url'] = 'sector_move_processing.php';
		$container['target_page'] = 'current_sector.php';
		$container['target_sector'] = $next_sector;

		$PHP_OUTPUT.=create_button($container, 'Follow plotted course - ' .  $next_sector . ' (' . $var['distance'] . ')');
	
		if ($ship->hasScanner()) {
	
			$PHP_OUTPUT.=('&nbsp;&nbsp;&nbsp;');
			$container = array();
			$container['url']			= 'skeleton.php';
			$container['body']			= 'sector_scan.php';
			$container['target_sector'] = $next_sector;
			$PHP_OUTPUT.=create_button($container, 'Scan');
	
		}
	}
}

$PHP_OUTPUT.= '</td></tr></table><br /><h2>Plotted Course</h2><br />';
$PHP_OUTPUT.= $full;

?>