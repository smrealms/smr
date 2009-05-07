<?
require_once(get_file_loc('SmrSector.class.inc'));
$sector =& SmrSector::getSector(SmrSession::$game_id, $player->getSectorID());
if (isset($_POST['to'])) $to = $_POST['to'];
else $to = $var['to'];
//allow hidden players (admins that don't play) to move without pinging, hitting mines, losing turns
if (in_array($player->getAccountID(), $HIDDEN_PLAYERS)) {
	$player->setLastSectorID($player->getSectorID());
	$player->setSectorID($to);
	$player->update();
	$sector->markVisited($player);
	$container['url'] = 'skeleton.php';
	$container['body'] = 'current_sector.php';
	forward($container);
}
$action = $_REQUEST['action'];
if ($action == 'No')
	forward(create_container('skeleton.php', $var['target_page']));

// get from and to sectors
$from = $player->getSectorID();

if (empty($to) || $to == '')
	create_error('Where do you want to go today?');

// get our rank
$rank_id = $account->get_rank();

// you can't move while on planet
if ($player->isLandedOnPlanet())
	create_error('You are on a planet! You must launch first!');

// check for turns
if ($player->getTurns() < 15)
	create_error('You don\'t have enough turns for that jump!');

// if no 'to' is given we forward to plot
if (empty($to))
	create_error('Where do you want to go today?');

if (!is_numeric($to))
	create_error('Please enter only numbers!');
	
if ($player->getSectorID() == $to)
	create_error('Hmmmm...if ' . $player->getSectorID() . '=' . $to . ' then that means...YOUR ALREADY THERE! *cough*your real smart*cough*');
	
if ($sector->hasForces()) {

	$db->query('SELECT * FROM sector_has_forces, player ' .
			   'WHERE sector_has_forces.game_id = player.game_id AND ' .
					 'sector_has_forces.owner_id = player.account_id AND ' .
					 'sector_has_forces.game_id = '.$player->getGameID().' AND ' .
					 'sector_has_forces.sector_id = '.$player->getSectorID().' AND ' .
					 'mines > 0 AND ' .
					 'owner_id != '.$player->getAccountID().' AND ' .
					 '(alliance_id != '.$player->getAllianceID().' OR alliance_id = 0) LIMIT 1');
	if($db->nextRecord())
	{
		create_error('You cant jump when there are unfriendly forces in the sector!');
	}
}

$targetExists = false;
$galaxies =& SmrGalaxy::getGameGalaxies($player->getGameID());
foreach($galaxies as &$galaxy)
{
	if($galaxy->contains($target))
		$targetExists = true;
} unset($galaxy);
if($targetExists===false)
	create_error('The target sector doesn\'t exist!');

// create sector object for target sector
$target_sector =& SmrSector::getSector(SmrSession::$game_id, $to);

// check if we would jump more than 1 warp
if ($sector->getGalaxyID() != $target_sector->getGalaxyID())
{
	//we need to see if they can jump this many gals
	$db->query('SELECT * FROM warp WHERE game_id = '.$player->getGameID());
	while($db->nextRecord())
	{
		$warp_sector1 =& SmrSector::getSector(SmrSession::$game_id, $db->getField('sector_id_1'));
		$warp_sector2 =& SmrSector::getSector(SmrSession::$game_id, $db->getField('sector_id_2'));

		if ($warp_sector1->getGalaxyID() == $target_sector->getGalaxyID() && $warp_sector2->getGalaxyID() == $sector->getGalaxyID())
			$allowed = true;
		if ($warp_sector1->getGalaxyID() == $sector->getGalaxyID() && $warp_sector2->getGalaxyID() == $target_sector->getGalaxyID())
			$allowed = true;
	}
}
else
	$allowed = true;

if (!$allowed)
	create_error('You can not jump that many galaxies away');

// for ingal jumps we use different algorithm
if ($sector->getGalaxyID() == $target_sector->getGalaxyID())
{
// include helper funtions
	require_once(get_file_loc('Plotter.class.inc'));
	$path =& Plotter::findDistanceToX(SmrSector::getSector($player->getGameID(),$to), $player->getSector(), true);
	
	if($path===false)
		create_error('Unable to plot from '.$start.' to '.$target.'.');

	// calculate the number of free sectors per jump
	$free_sector = 15 + floor($player->getLevelID() / 10);

	// the rest gets a 10% failure per sector
	if ($distance > $free_sector)
		$failure_chance = 10 * ($path->getRelativeDistance() - $free_sector);
	else
		$failure_chance = 0;

	$failure_distance = round($failure_chance / 10);
}
else
{
	$failure_chance = 75;
	$failure_distance = round(0.1 * mt_rand(10, 30 + (50 - $player->getLevelID())));

}

if (mt_rand(1, 100) <= $failure_chance) {

	// we missed the sector

	// initialize the queue. all sectors are queued here during the iterations
	$sector_queue = array();

	// keeps the distance to the start sector
	$sector_distance = array();

	// putting start sector in queues
	array_push($sector_queue, $target_sector->getSectorID());
	$sector_distance[$target_sector->getSectorID()] = 0;

	while (sizeof($sector_queue) > 0) {

		// get current sector and
		$curr_sector_id = array_shift($sector_queue);

		// get the distance for this sector from the source
		$distance = $sector_distance[$curr_sector_id];

		// create a new sector object
		$curr_sector =& SmrSector::getSector(SmrSession::$game_id, $curr_sector_id);

		// enqueue all neighbours
		if ($curr_sector->getLinkUp() > 0 && (!isset($sector_distance[$curr_sector->getLinkUp()]) || $sector_distance[$curr_sector->getLinkUp()] > $distance + 1) && $failure_distance > $distance) {

			array_push($sector_queue, $curr_sector->getLinkUp());
			$sector_distance[$curr_sector->getLinkUp()] = $distance + 1;

		}

		if ($curr_sector->getLinkDown() > 0 && (!isset($sector_distance[$curr_sector->getLinkDown()]) || $sector_distance[$curr_sector->getLinkDown()] > $distance + 1) && $failure_distance > $distance) {

			array_push($sector_queue, $curr_sector->getLinkDown());
			$sector_distance[$curr_sector->getLinkDown()] = $distance + 1;

		}

		if ($curr_sector->getLinkLeft() > 0 && (!isset($sector_distance[$curr_sector->getLinkLeft()]) || $sector_distance[$curr_sector->getLinkLeft()] > $distance + 1) && $failure_distance > $distance) {

			array_push($sector_queue, $curr_sector->getLinkLeft());
			$sector_distance[$curr_sector->getLinkLeft()] = $distance + 1;

		}

		if ($curr_sector->getLinkRight() > 0 && (!isset($sector_distance[$curr_sector->getLinkRight()]) || $sector_distance[$curr_sector->getLinkRight()] > $distance + 1) && $failure_distance > $distance) {

			array_push($sector_queue, $curr_sector->getLinkRight());
			$sector_distance[$curr_sector->getLinkRight()] = $distance + 1;

		}

	}

	// returns an array that only contain the failure distance
	while (empty($temp_array)) {
		
		$temp_array = array_keys($sector_distance, $failure_distance);
		//gal isn't big enough for that big of a misjump...take 1 of the failure
		$failure_distance -= 1;
		
	}

    // get one element
	$player->setSectorID($temp_array[ array_rand($temp_array) ]);

} else {

	// we hit it. exactly
	$player->setSectorID($target_sector->getSectorID());

}

// log action
$account->log(5, 'Jumps to sector: '.$to.' but hits: '.$player->getSectorID(), $sector->getSectorID());

// send scout msg
$sector->leavingSector($player,MOVEMENT_JUMP);

//set the last sector
$player->setLastSectorID($sector->getSectorID());

// Move the user around (Must be done while holding both sector locks)
$player->takeTurns(15,15);
//$player->sector_change();
//$player->detected = 'false';
$player->update();

// We need to release the lock on our old sector
release_lock();

// We need a lock on the new sector so that more than one person isn't hitting the same mines
acquire_lock($player->getSectorID());



// delete plotted course
$player->deletePlottedCourse();

// get new sector object
$sector =& SmrSector::getSector($player->getGameID(), $player->getSectorID());

// make current sector visible to him
$sector->markVisited($player);

// send scout msg
$sector->enteringSector($player);

$db->query('SELECT * FROM sector_has_forces, player ' .
		   'WHERE sector_has_forces.game_id = player.game_id AND ' .
				 'sector_has_forces.owner_id = player.account_id AND ' .
				 'sector_has_forces.game_id = '.$player->getGameID().' AND ' .
				 'sector_has_forces.sector_id = '.$player->getSectorID().' AND ' .
				 'mines > 0 AND ' .
				 'owner_id != '.$player->getAccountID().' AND ' .
				 '(alliance_id != '.$player->getAllianceID().' OR alliance_id = 0)');

while ($db->nextRecord())
{
	if ($player->getNewbieTurns() > 0) {

		$container = array();
		$container['url']		= 'skeleton.php';
		$container['body']		= 'current_sector.php';;
		$container['msg']		= 'You have just flown past a sprinkle of mines.<br />Because of your newbie status you have been spared from the harsh reality of the forces.';
		forward($container);
	}
	else
	{
    	$owner_id = $db->getField('owner_id');
    	include('forces_minefield_processing.php');
    	exit;

	}

}

forward(create_container('skeleton.php', $var['target_page']));

?>
