<?php
$sector =& $player->getSector();
if (isset($_REQUEST['target'])) $target = trim($_REQUEST['target']);
else $target = $var['target'];
//allow hidden players (admins that don't play) to move without pinging, hitting mines, losing turns
if (in_array($player->getAccountID(), Globals::getHiddenPlayers()))
{
	$player->setSectorID($target);
	$player->update();
	$sector->markVisited($player);
	forward(create_container('skeleton.php', 'current_sector.php'));
}
$action = $_REQUEST['action'];
if ($action == 'No')
	forward(create_container('skeleton.php', $var['target_page']));

// get from and to sectors
$from = $player->getSectorID();

if (empty($target) || $target == '')
	create_error('Where do you want to go today?');

// get our rank
$rank_id = $account->getRank();

// you can't move while on planet
if ($player->isLandedOnPlanet())
	create_error('You are on a planet! You must launch first!');

// if no 'target' is given we forward to plot
if (empty($target))
	create_error('Where do you want to go today?');

if (!is_numeric($target))
	create_error('Please enter only numbers!');
	
if ($player->getSectorID() == $target)
	create_error('Hmmmm...if ' . $player->getSectorID() . '=' . $target . ' then that means...YOU\'RE ALREADY THERE! *cough*you\'re real smart*cough*');

if ($sector->hasForces())
{
	$sectorForces =& $sector->getForces();
	foreach($sectorForces as &$forces)
	{
		if($forces->hasMines() && !$player->forceNAPAlliance($forces->getOwner()))
		{
			create_error('You cant jump when there are unfriendly forces in the sector!');
		}
	} unset($forces);
}

if(!SmrGalaxy::getGalaxyContaining($player->getGameID(), $target))
	create_error('The target sector doesn\'t exist!');

// create sector object for target sector
$targetSector =& SmrSector::getSector($player->getGameID(), $target);

require_once(get_file_loc('Plotter.class.inc'));
$path =& Plotter::findDistanceToX($targetSector, $player->getSector(), true);
if($path===false)
	create_error('Unable to plot from '.$start.' to '.$target.'.');

// send scout msg
$sector->leavingSector($player,MOVEMENT_JUMP);

// Move the user around
// TODO: (Must be done while holding both sector locks)
$distance = $path->getRelativeDistance();
$turnsToJump = max(TURNS_JUMP_MINIMUM, round($distance * TURNS_PER_JUMP_DISTANCE));

// check for turns
if ($player->getTurns() < $turnsToJump)
	create_error('You don\'t have enough turns for that jump!');

$maxMisjump = max(0,round(($distance - $turnsToJump) * MISJUMP_DISTANCE_DIFF_FACTOR / (1 + $player->getLevelID() * MISJUMP_LEVEL_FACTOR)));
$misjump = mt_rand(0,$maxMisjump);
if ($misjump > 0)
{ // we missed the sector
	$distances = Plotter::findDistanceToX('Distance', $targetSector, false, null, null, $misjump);
	while(count($distances[$misjump]) == 0)
	{
		$misjump--;
	}
		
	$misjumpSector = array_rand($distances[$misjump]);
	if($misjumpSector == null)
		throw new Exception('Misjump sector is null, distances: ' . var_export ($distances, true));
	$player->setSectorID($misjumpSector);
	unset($distances);
}
else
{ // we hit it. exactly
	$player->setSectorID($targetSector->getSectorID());
}
$player->takeTurns($turnsToJump,$turnsToJump);

// log action
$account->log(LOG_TYPE_MOVEMENT, 'Jumps to sector: '.$target.' but hits: '.$player->getSectorID(), $sector->getSectorID());

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
$sector =& $player->getSector();

// make current sector visible to him
$sector->markVisited($player);

// send scout msg
$sector->enteringSector($player,MOVEMENT_JUMP);

$sectorForces =& $sector->getForces();
$mineOwnerID = false;
foreach($sectorForces as &$forces)
{
	if(!$mineOwnerID && $forces->hasMines() && !$player->forceNAPAlliance($forces->getOwner()))
	{
		$mineOwnerID = $forces->getOwnerID();
		break;
	}
} unset($forces);
if($mineOwnerID)
{
	if ($player->hasNewbieTurns())
	{
		$container = create_container('skeleton.php', 'current_sector.php');
		$container['msg'] = 'You have just flown past a sprinkle of mines.<br />Because of your newbie status you have been spared from the harsh reality of the forces.';
		forward($container);
	}
	else
	{
		$owner_id = $mineOwnerID;
		require_once('forces_minefield_processing.php');
		exit;
	}
}

forward(create_container('skeleton.php', $var['target_page']));
?>