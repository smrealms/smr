<?php

if ($player->getNewbieTurns() > 0)
	create_error('You can\'t take/drop forces under newbie protection!');

if ($player->isLandedOnPlanet())
	create_error('You must first launch to drop forces');

// take either from container or request, prefer container
$drop_mines			= isset($var['drop_mines'])			? $var['drop_mines']			: (isset($_REQUEST['drop_mines'])			? trim($_REQUEST['drop_mines']) : 0);
$drop_combat_drones = isset($var['drop_combat_drones'])	? $var['drop_combat_drones']	: (isset($_REQUEST['drop_combat_drones'])	? trim($_REQUEST['drop_combat_drones']) : 0);
$drop_scout_drones	= isset($var['drop_scout_drones'])	? $var['drop_scout_drones']		: (isset($_REQUEST['drop_scout_drones'])	? trim($_REQUEST['drop_scout_drones']) : 0);
$take_mines			= isset($var['take_mines'])			? $var['take_mines']			: (isset($_REQUEST['take_mines'])			? trim($_REQUEST['take_mines']) : 0);
$take_combat_drones	= isset($var['take_combat_drones'])	? $var['take_combat_drones']	: (isset($_REQUEST['take_combat_drones'])	? trim($_REQUEST['take_combat_drones']) : 0);
$take_scout_drones	= isset($var['take_scout_drones'])	? $var['take_scout_drones']		: (isset($_REQUEST['take_scout_drones'])	? trim($_REQUEST['take_scout_drones']) : 0);

// do we have numbers?
if (!empty($drop_mines) && !is_numeric($drop_mines)) create_error('Only numbers as input allowed');
if (!empty($drop_combat_drones) && !is_numeric($drop_combat_drones)) create_error('Only numbers as input allowed');
if (!empty($drop_scout_drones) && !is_numeric($drop_scout_drones)) create_error('Only numbers as input allowed');
if (!empty($take_mines) && !is_numeric($take_mines)) create_error('Only numbers as input allowed');
if (!empty($take_combat_drones) && !is_numeric($take_combat_drones)) create_error('Only numbers as input allowed');
if (!empty($take_scout_drones) && !is_numeric($take_scout_drones)) create_error('Only numbers as input allowed');

// round if necessary
$drop_mines = round($drop_mines);
$drop_combat_drones = round($drop_combat_drones);
$drop_scout_drones = round($drop_scout_drones);
$take_mines = round($take_mines);
$take_combat_drones = round($take_combat_drones);
$take_scout_drones = round($take_scout_drones);

// so how many forces do we take/add per type?
$change_mines = $drop_mines - $take_mines;
$change_combat_drones = $drop_combat_drones - $take_combat_drones;
$change_scout_drones = $drop_scout_drones - $take_scout_drones;

// do we have any action at all?
if ($change_mines == 0 && $change_combat_drones == 0 && $change_scout_drones == 0)
	create_error('You want to add/remove 0 forces?');

if ($sector->hasLocation())
	create_error('You can\'t drop forces in a sector with a location!');
	
require_once(get_file_loc('SmrForce.class.inc'));
$forces =& SmrForce::getForce($player->getGameID(), $player->getSectorID(), $var['owner_id']);

include(get_file_loc('mine_change.php'));
// check max on that stack
if ($forces->getMines() + $change_mines > 50)
{
	$change_mines = 50 - $forces->getMines();
//	create_error('This stack can only take up to 50 mines!');
}

if ($forces->getCDs() + $change_combat_drones > 50)
{
	$change_combat_drones = 50 - $forces->getCDs();
//	create_error('This stack can only take up to 50 combat drones!');
}

if ($forces->getSDs() + $change_scout_drones > 5)
{
	$change_scout_drones = 5 - $forces->getSDs();
//	create_error('This stack can only take up to 5 scout drones!');
}

// combat drones
if ($change_combat_drones != 0)
{
	// we can't take more forces than are in sector
	if ($forces->getCDs() + $change_combat_drones < 0)
	{
		create_error('You can\'t take more combat drones than are on this stack!');
	}

	if ($ship->getCDs() - $change_combat_drones > $ship->getMaxCDs())
	{
		create_error('Your ships supports no more than ' . $ship->getMaxCDs() . ' combat drones!');
	}

	if ($ship->getCDs() - $change_combat_drones < 0)
	{
		create_error('You can\'t drop more combat drones than you carry!');
	}

	// remove from ship
	if($change_combat_drones>0)
		$ship->decreaseCDs($change_combat_drones,true);
	else
		$ship->increaseCDs(-$change_combat_drones,true);

	// drop in sector
	if($change_combat_drones>0)
		$forces->addCDs($change_combat_drones);
	else
		$forces->takeCDs(-$change_combat_drones);

}

if ($change_scout_drones != 0) {

	// we can't take more forces than are in sector
	if ($forces->getSDs() + $change_scout_drones < 0)
		create_error('You can\'t take more scout drones than are on this stack!');

	if ($ship->getSDs() - $change_scout_drones > $ship->getMaxSDs())
		create_error('Your ships supports no more than ' . $ship->getMaxSDs() . ' scout drones!');

	if ($ship->getSDs() - $change_scout_drones < 0)
		create_error('You can\'t drop more scout drones than you carry!');

	// remove from ship
	if($change_scout_drones>0)
		$ship->decreaseSDs($change_scout_drones);
	else
		$ship->increaseSDs(-$change_scout_drones);

	// drop in sector
	if($change_scout_drones>0)
		$forces->addSDs($change_scout_drones);
	else
		$forces->takeSDs(-$change_scout_drones);

}

if ($change_mines != 0) {

	// we can't take more forces than are in sector
	if ($forces->getMines() + $change_mines < 0)
		create_error('You can\'t take more mines than are on this stack!');

	if ($ship->getMines() - $change_mines > $ship->getMaxMines())
		create_error('Your ships supports no more than ' . $ship->getMaxMines() . ' mines!');

	if ($ship->getMines() - $change_mines < 0)
		create_error('You can\'t drop more mines than you carry!');

	// remove from ship
	if($change_mines>0)
		$ship->decreaseMines($change_mines);
	else
		$ship->increaseMines(-$change_mines);

	// drop in sector
	if($change_mines>0)
		$forces->addMines($change_mines);
	else
		$forces->takeMines(-$change_mines);

}

// message to send out
if ($forces->getOwnerID() != $player->getAccountID() && $forces->getOwner()->isForceDropMessages())
{
	$mines_message = '';
	if ($change_mines > 0)
		$mines_message = 'added ' . ($drop_mines - $take_mines) . ' mine';
	elseif ($change_mines < 0)
		$mines_message = 'removed ' . abs($drop_mines - $take_mines) . ' mine';
	//add s to mine if necesary
	if (abs($change_mines) > 1)
		$mines_message .= 's';

	if ($change_combat_drones > 0)
		$combat_drones_message = ($change_mines <= 0 ?'added ':'') . ($drop_combat_drones - $take_combat_drones) . ' combat drone';
	elseif ($change_combat_drones < 0)
		$combat_drones_message = ($change_mines >= 0 ?'removed ':'') . abs($drop_combat_drones - $take_combat_drones) . ' combat drone';
	//add s to drone if necesary
	if (abs($change_combat_drones) > 1)
		$combat_drones_message .= 's';

	if ($change_scout_drones > 0)
	{
		$scout_drones_message='';
		if((isset($combat_drones_message) && $change_combat_drones < 0) || (!isset($combat_drones_message) && $change_mines <= 0))
			$scout_drones_message = 'added ';
		$scout_drones_message .= ($drop_scout_drones - $take_scout_drones) . ' scout drone';
	}
	elseif ($change_scout_drones < 0)
	{
		$scout_drones_message='';
		if((isset($combat_drones_message) && $change_combat_drones > 0) || (!isset($combat_drones_message) && $change_mines >= 0))
			$scout_drones_message = 'removed ';
		$scout_drones_message .= abs($drop_scout_drones - $take_scout_drones) . ' scout drone';
	}
	//add s to drone if necesary
	if (abs($change_scout_drones) > 1)
		$scout_drones_message .= 's';

	// now compile it together
	$message = $player->getPlayerName().' has ' . $mines_message;

	if (!empty($mines_message) && isset($combat_drones_message) && !isset($scout_drones_message))
		$message .= ' and '.$combat_drones_message;
	elseif (!empty($mines_message) && isset($combat_drones_message))
		$message .= ', '.$combat_drones_message;
	elseif (empty($mines_message) && isset($combat_drones_message))
		$message .= $combat_drones_message;

	if (!empty($mines_message) && isset($combat_drones_message) && isset($scout_drones_message))
		$message .= ', and '.$scout_drones_message;
	elseif ((!empty($mines_message) || isset($combat_drones_message)) && isset($scout_drones_message))
		$message .= ' and '.$scout_drones_message;
	elseif (empty($mines_message) && !isset($combat_drones_message) && isset($scout_drones_message))
		$message .= $scout_drones_message;

	if($change_mines >= 0 && $change_combat_drones >= 0 && $change_scout_drones >= 0)
		$message .= ' to';
	elseif($change_mines <= 0 && $change_combat_drones <= 0 && $change_scout_drones <= 0)
		$message .= ' from';
	else
		$message .= ' from/to';
		
	$message .= ' your stack in sector #'.$forces->getSectorID();

	$player->sendMessage($forces->getOwnerID(), MSG_SCOUT, $message, false);
}

$account->log(9, $change_combat_drones.' combat drones, '.$change_scout_drones.' scout drones, '.$change_mines.' mines', $player->getSectorID());

$forces->updateExpire();
$forces->update(); // Needs to be in db to show up on CS instantly when querying sector forces
$ship->removeUnderAttack();

forward(create_container('skeleton.php', 'current_sector.php'));

?>
