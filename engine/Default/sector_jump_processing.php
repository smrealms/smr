<?php
require_once(get_file_loc('SmrSector.class.inc'));
$sector =& SmrSector::getSector(SmrSession::$game_id, $player->getSectorID());
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
$rank_id = $account->get_rank();

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

if ($sector->hasEnemyForces($player))
	create_error('You cant jump when there are unfriendly forces in the sector!');

if(!SmrGalaxy::getGalaxyContaining($player->getGameID(), $target))
	create_error('The target sector doesn\'t exist!');

// create sector object for target sector
$targetSector =& SmrSector::getSector($player->getGameID(), $target);

$path =& Plotter::findDistanceToX($targetSector, $player->getSector(), true);
if($path===false)
	create_error('Unable to plot from '.$start.' to '.$target.'.');

// send scout msg
$sector->leavingSector($player,MOVEMENT_JUMP);

// Move the user around
// TODO: (Must be done while holding both sector locks)
$distance = $path->getRelativeDistance();
$turnsToJump = min(TURNS_JUMP_MINIMUM, round($distance * TURNS_PER_JUMP_DISTANCE));

// check for turns
if ($player->getTurns() < $turnsToJump)
	create_error('You don\'t have enough turns for that jump!');

$maxMisjump = max(0,round(($turnsToJump - $distance) * 1.5 / (1 + $player->getLevel() * MISJUMP_LEVEL_FACTOR)));
$misjump = mt_rand(0,$maxMisjump);

if ($misjump > 0)
{ // we missed the sector
	$distances = Plotter::findDistanceToX('Distance', $sector, false, null, null, $misjump);
	$player->setSectorID(array_rand($distances));
}
else
{ // we hit it. exactly
	$player->setSectorID($targetSector->getSectorID());
}
$player->takeTurns($turnsToJump,$turnsToJump);

// log action
$account->log(5, 'Jumps to sector: '.$target.' but hits: '.$player->getSectorID(), $sector->getSectorID());

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
    	include('forces_minefield_processing.php');
    	exit;
	}
}

forward(create_container('skeleton.php', $var['target_page']));
?>