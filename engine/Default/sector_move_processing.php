<?php

if ($var['target_sector'] == $player->getSectorID())
	forward(create_container('skeleton.php', $var['target_page']));

if($sector->getWarp() == $var['target_sector'])
	$turns = TURNS_PER_WARP;
else
	$turns = TURNS_PER_SECTOR;
//allow hidden players (admins that don't play) to move without pinging, hitting mines, losing turns
if (in_array($player->getAccountID(), Globals::getHiddenPlayers()))
{
	//update plot
	if ($player->hasPlottedCourse())
	{
		$path =& $player->getPlottedCourse();
		if ($path->getNextOnPath() == $var['target_sector'])
		{
			$path->followPath($sector->getWarp() == $var['target_sector']);
			$player->setPlottedCourse($path);
		}
		else
			$player->deletePlottedCourse();
	}
	
	//make them pop on CPL
	$player->updateLastCPLAction();
	$player->setSectorID($var['target_sector']);
	$player->update();
					
	// get new sector object
	$sector =& $player->getSector();
	$sector->markVisited($player);
	forward(create_container('skeleton.php', $var['target_page']));
}
$action = '';
if(isset($_REQUEST['action']))
{
	$action = $_REQUEST['action'];
	if ($action == 'No')
		forward(create_container('skeleton.php', $var['target_page']));
}

// you can't move while on planet
if ($player->isLandedOnPlanet())
	create_error('You can\'t activate your engine while you are on a planet!');

if ($player->getTurns() < $turns)
	create_error('You don\'t have enough turns to move!');

if (!$sector->isLinked($var['target_sector']))
	create_error('You cannot move to that sector!');

if ($player->getLastSectorID() != $var['target_sector'])
{
	$sectorForces =& $sector->getForces();
	Sorter::sortByNumMethod($sectorForces,'getMines',true);
	$mine_owner_id = false;
	foreach($sectorForces as &$forces)
	{
		if(!$mine_owner_id && $forces->hasMines() && !$player->forceNAPAlliance($forces->getOwner()))
		{
			$mine_owner_id = $forces->getOwnerID();
			break;
		}
	} unset($forces);
	// set last sector
	$player->setLastSectorID($var['target_sector']);
	
	if($mine_owner_id)
	{
		if ($player->hasNewbieTurns())
		{
			$turns = $sectorForces[$mine_owner_id]->getBumpTurnCost();
			$player->takeTurns($turns,$turns);
			$container = create_container('skeleton.php', 'current_sector.php');
			$container['msg']= 'You have just flown past a sprinkle of mines.<br />Because of your newbie status you have been spared from the harsh reality of the forces.<br />It has cost you ' . $turns.' turn'.($turns==1?'':'s') . ' to navigate the minefield safely';
			forward($container);
		}
		else
		{
			$owner_id = $mine_owner_id;
			include('forces_minefield_processing.php');
			return;
		}
	}
}

// check if this came from a plotted course from db
if ($player->hasPlottedCourse())
{
	$path =& $player->getPlottedCourse();
	if ($path->getNextOnPath() == $var['target_sector'])
	{
		$path->followPath($sector->getWarp() == $var['target_sector']);
		$player->setPlottedCourse($path);
	}
	else
		$player->deletePlottedCourse();
}

// log action
$player->actionTaken('WalkSector',array('Sector'=>&$sector));

// send scout msg
$sector->leavingSector($player,MOVEMENT_WALK);

// Move the user around
// TODO: (Must be done while holding both sector locks)
$player->setSectorID($var['target_sector']);
$player->takeTurns($turns,$turns);
$player->update();

// We need to release the lock on our old sector
release_lock();

// We need a lock on the new sector so that more than one person isn't hitting the same mines
acquire_lock($var['target_sector']);

// get new sector object
$sector =& $player->getSector();

//add that the player explored here if it hasnt been explored...for HoF
if (!$sector->isVisited($player))
{
	$player->increaseExperience(EXPLORATION_EXPERIENCE);
	$player->increaseHOF(EXPLORATION_EXPERIENCE,array('Movement','Exploration Experience Gained'), HOF_ALLIANCE);
	$player->increaseHOF(1,array('Movement','Sectors Explored'), HOF_ALLIANCE);
}
// make current sector visible to him
$sector->markVisited($player);

// send scout msgs
$sector->enteringSector($player,MOVEMENT_WALK);

$sectorForces =& $sector->getForces();
$mine_owner_id = false;
Sorter::sortByNumMethod($sectorForces,'getMines',true);
foreach($sectorForces as &$forces)
{
	if(!$mine_owner_id && $forces->hasMines() && !$player->forceNAPAlliance($forces->getOwner()))
	{
		$mine_owner_id = $forces->getOwnerID();
		break;
	}
} unset($forces);

if ($mine_owner_id)
{
	if ($player->hasNewbieTurns())
	{
		$turns = $sectorForces[$mine_owner_id]->getBumpTurnCost();
		$player->takeTurns($turns,$turns);
		create_container('skeleton.php', 'current_sector.php');
		$container['msg']= 'You have just flown past a sprinkle of mines.<br />Because of your newbie status you have been spared from the harsh reality of the forces.<br />It has cost you ' . $turns.' turn'.($turns==1?'':'s') . ' to navigate the minefield safely.';
		forward($container);
	}
	else
	{
		$owner_id = $mine_owner_id;
		include('forces_minefield_processing.php');
		return;
	}
}

// otherwise
forward(create_container('skeleton.php', $var['target_page']));
?>