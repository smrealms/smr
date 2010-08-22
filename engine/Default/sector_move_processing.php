<?php

if ($var['target_sector'] == $player->getSectorID())
	forward(create_container('skeleton.php', $var['target_page']));

$db->query('SELECT galaxy_id FROM sector WHERE sector_id=' . $var['target_sector'] . ' AND game_id=' . SmrSession::$game_id . ' LIMIT 1');
$db->nextRecord();
$targetGalaxyID = $db->getField('galaxy_id');

if($sector->getWarp() == $var['target_sector'])
	$turns = TURNS_PER_WARP;
else
	$turns = TURNS_PER_SECTOR;
//allow hidden players (admins that don't play) to move without pinging, hitting mines, losing turns
if (in_array($player->getAccountID(), Globals::getHiddenPlayers()))
{
	//for plotted course
	$player->setLastSectorID($player->getSectorID());
	//make them pop on CPL
	$player->updateLastCPLAction();
	$player->setSectorID($var['target_sector']);
	$player->update();
	
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
					
	// get new sector object
	$sector =& SmrSector::getSector($player->getGameID(), $player->getSectorID());
	$sector->markVisited($player);
	$container['url'] = 'skeleton.php';
	$container['body'] = $var['target_page'];
	forward($container);
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

require_once(get_file_loc('Sorter.class.inc'));
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

if ($player->getLastSectorID() != $var['target_sector'] && $mine_owner_id)
{
	// set last sector
	$player->setLastSectorID($var['target_sector']);
	
	if ($player->hasNewbieTurns())
	{
		$container['url']	= 'skeleton.php';
		$container['body']	= 'current_sector.php';
		$container['msg']	= 'You have just flown past a sprinkle of mines.<br />Because of your newbie status you have been spared from the harsh reality of the forces.<br />It has cost you ';
		$turns = $sectorForces[$mine_owner_id]->getBumpTurnCost();
		$container['msg'] .= $turns.' turn'.($turns==1?'':'s');
		
		$player->takeTurns($turns,min($turns,1));
		
		$container['msg'] .= ' to navigate the minefield safely';
		forward($container);
	}
	else
	{
		$owner_id = $mine_owner_id;
		include('forces_minefield_processing.php');
		return;
	}
}

//set the last sector
$player->setLastSectorID($player->getSectorID());

// log action
$account->log(5, 'Moves to sector: ' . $var['target_sector'], $player->getSectorID());

// send scout msg
$sector->leavingSector($player,MOVEMENT_WALK);

// Move the user around (Must be done while holding both sector locks)
$player->setSectorID($var['target_sector']);
$player->takeTurns($turns,$turns);
$player->detected = 'false';
$player->update();

// We need to release the lock on our old sector
release_lock();

// We need a lock on the new sector so that more than one person isn't hitting the same mines
acquire_lock($var['target_sector']);

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

// get new sector object
$sector =& SmrSector::getSector($player->getGameID(), $player->getSectorID());

//add that the player explored here if it hasnt been explored...for HoF
if (!$sector->isVisited($player))
{
	$player->increaseExperience(EXPLORATION_EXPERIENCE);
	$player->increaseHOF(EXPLORATION_EXPERIENCE,array('Movement','Exploration Experience Gained'), HOF_PUBLIC);
	$player->increaseHOF(1,array('Movement','Sectors Explored'), HOF_PUBLIC);
}
// make current sector visible to him
$sector->markVisited($player);

// send scout msgs
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

$sector->enteringSector($player,MOVEMENT_WALK);

if ($mine_owner_id)
{
	if ($player->hasNewbieTurns())
	{
		$container['url']	= 'skeleton.php';
		$container['body']	= 'current_sector.php';
		$container['msg']	= 'You have just flown past a sprinkle of mines.<br />Because of your newbie status you have been spared from the harsh reality of the forces.<br />It has cost you ';
		$turns = $sectorForces[$mine_owner_id]->getBumpTurnCost();
		$container['msg'] .= $turns.' turn'.($turns==1?'':'s');
		
		$player->takeTurns($turns,min($turns,1));
		
		$container['msg'] .= ' to navigate the minefield safely';
		forward($container);
	}
	
	$player->update();
	
	if($player->getNewbieTurns() > 0)
	{
		$container['msg'] .= ' to navigate the minefield safely';
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
$container['url'] = 'skeleton.php';
$container['body'] = $var['target_page'];
forward($container);
?>