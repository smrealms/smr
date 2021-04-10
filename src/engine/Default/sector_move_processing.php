<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$player = $session->getPlayer();
$sector = $player->getSector();

if (!$player->getGame()->hasStarted()) {
	create_error('You cannot move until the game has started!');
}

if ($var['target_sector'] == $player->getSectorID()) {
	Page::create('skeleton.php', $var['target_page'])->go();
}

if ($sector->getWarp() == $var['target_sector']) {
	$turns = TURNS_PER_WARP;
} else {
	$turns = TURNS_PER_SECTOR;
}

//allow hidden players (admins that don't play) to move without pinging, hitting mines, losing turns
if (in_array($player->getAccountID(), Globals::getHiddenPlayers())) {
	//make them pop on CPL
	$player->updateLastCPLAction();
	$player->setSectorID($var['target_sector']);
	$player->update();

	// get new sector object
	$sector = $player->getSector();
	$sector->markVisited($player);
	Page::create('skeleton.php', $var['target_page'])->go();
}

// you can't move while on planet
if ($player->isLandedOnPlanet()) {
	create_error('You can\'t activate your engine while you are on a planet!');
}

if ($player->getTurns() < $turns) {
	create_error('You don\'t have enough turns to move!');
}

if (!$sector->isLinked($var['target_sector'])) {
	create_error('You cannot move to that sector!');
}

// If not moving to your "green sector", you might hit mines...
if ($player->getLastSectorID() != $var['target_sector']) {
	// Update the "green sector"
	$player->setLastSectorID($var['target_sector']);
	require('sector_mines.inc.php');
}

// log action
$targetSector = SmrSector::getSector($player->getGameID(), $var['target_sector']);
$player->actionTaken('WalkSector', array('Sector' => $targetSector));

// send scout msg
$sector->leavingSector($player, MOVEMENT_WALK);

// Move the user around
// TODO: (Must be done while holding both sector locks)
$player->setSectorID($var['target_sector']);
$player->takeTurns($turns, $turns);
$player->update();

// We need to release the lock on our old sector
release_lock();

// We need a lock on the new sector so that more than one person isn't hitting the same mines
acquire_lock($var['target_sector']);

// get new sector object
$sector = $player->getSector();

//add that the player explored here if it hasnt been explored...for HoF
if (!$sector->isVisited($player)) {
	$player->increaseExperience(EXPLORATION_EXPERIENCE);
	$player->increaseHOF(EXPLORATION_EXPERIENCE, array('Movement', 'Exploration Experience Gained'), HOF_ALLIANCE);
	$player->increaseHOF(1, array('Movement', 'Sectors Explored'), HOF_ALLIANCE);
}
// make current sector visible to him
$sector->markVisited($player);

// send scout msgs
$sector->enteringSector($player, MOVEMENT_WALK);

// If you bump into mines while entering the target sector...
require('sector_mines.inc.php');

// otherwise
Page::create('skeleton.php', $var['target_page'])->go();
