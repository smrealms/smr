<?php

if ($player->getNewbieTurns() > 0) {
	create_error('You can\'t take/drop forces under newbie protection!');
}

if ($player->isLandedOnPlanet()) {
	create_error('You must first launch to drop forces!');
}

if ($sector->hasLocation()) {
	create_error('You can\'t drop forces in a sector with a location!');
}

function getInputAmount($name) {
	global $var;
	$value = $var[$name] ?? ($_REQUEST[$name] ?? 0);
	// Empty strings are a valid HTML5 "number" input type value
	if (empty($value)) {
		$value = 0;
	}
	// Any other non-numeric value is bypassing the HTML5 input type validator
	if (!is_numeric($value)) {
		throw new Exception('Expected numeric value for ' . $name . ', got ' . $value);
	}
	return round($value);
}

// take either from container or request, prefer container
$drop_mines = getInputAmount('drop_mines');
$take_mines = getInputAmount('take_mines');
$drop_combat_drones = getInputAmount('drop_combat_drones');
$take_combat_drones = getInputAmount('take_combat_drones');
$drop_scout_drones = getInputAmount('drop_scout_drones');
$take_scout_drones = getInputAmount('take_scout_drones');

// so how many forces do we take/add per type?
$change_mines = $drop_mines - $take_mines;
$change_combat_drones = $drop_combat_drones - $take_combat_drones;
$change_scout_drones = $drop_scout_drones - $take_scout_drones;

$forces = SmrForce::getForce($player->getGameID(), $player->getSectorID(), $var['owner_id']);

// check max on that stack
$at_max = false;
if ($forces->getMines() + $change_mines > 50) {
	$change_mines = 50 - $forces->getMines();
	$at_max = $change_mines == 0;
}

if ($forces->getCDs() + $change_combat_drones > 50) {
	$change_combat_drones = 50 - $forces->getCDs();
	$at_max = $change_combat_drones == 0;
}

if ($forces->getSDs() + $change_scout_drones > 5) {
	$change_scout_drones = 5 - $forces->getSDs();
	$at_max = $change_scout_drones == 0;
}

// Check if the delta is 0 after applying the caps, in case by applying the caps we actually changed it to 0.
if ($change_mines == 0 && $change_combat_drones == 0 && $change_scout_drones == 0) {
	if ($at_max) {
		// If no forces added only because the stack is full
		create_error('This stack can\'t hold any more of those forces!');
	} else {
		// If drop == take
		create_error('You want to add/remove 0 forces?');
	}
}

// NOTE: we do all error checking first, because any forces we remove from
// the ship will vanish if we hit an error afterwards. This is because we
// don't update the force stack expire time until the end of this script.
// Force stacks without an updated expire time are automatically removed.
//
// We don't make the expire time update part of every force change internally
// because those functions are used to remove forces via combat (which isn't
// supposed to update the expire time).

if ($change_combat_drones != 0) {
	// we can't take more forces than are in sector
	if ($forces->getCDs() + $change_combat_drones < 0) {
		create_error('You can\'t take more combat drones than are on this stack!');
	}

	if ($ship->getCDs() - $change_combat_drones > $ship->getMaxCDs()) {
		create_error('Your ships supports no more than ' . $ship->getMaxCDs() . ' combat drones!');
	}

	if ($ship->getCDs() - $change_combat_drones < 0) {
		create_error('You can\'t drop more combat drones than you carry!');
	}
}

if ($change_scout_drones != 0) {
	// we can't take more forces than are in sector
	if ($forces->getSDs() + $change_scout_drones < 0) {
		create_error('You can\'t take more scout drones than are on this stack!');
	}

	if ($ship->getSDs() - $change_scout_drones > $ship->getMaxSDs()) {
		create_error('Your ships supports no more than ' . $ship->getMaxSDs() . ' scout drones!');
	}

	if ($ship->getSDs() - $change_scout_drones < 0) {
		create_error('You can\'t drop more scout drones than you carry!');
	}
}

if ($change_mines != 0) {
	// we can't take more forces than are in sector
	if ($forces->getMines() + $change_mines < 0) {
		create_error('You can\'t take more mines than are on this stack!');
	}

	if ($ship->getMines() - $change_mines > $ship->getMaxMines()) {
		create_error('Your ships supports no more than ' . $ship->getMaxMines() . ' mines!');
	}

	if ($ship->getMines() - $change_mines < 0) {
		create_error('You can\'t drop more mines than you carry!');
	}
}

// All error checking is done, so now update the ship/force

if ($change_combat_drones != 0) {
	if ($change_combat_drones > 0) {
		$ship->decreaseCDs($change_combat_drones, true);
		$forces->addCDs($change_combat_drones);
	}
	else {
		$ship->increaseCDs(-$change_combat_drones, true);
		$forces->takeCDs(-$change_combat_drones);
	}
}

if ($change_scout_drones != 0) {
	if ($change_scout_drones > 0) {
		$ship->decreaseSDs($change_scout_drones);
		$forces->addSDs($change_scout_drones);
	}
	else {
		$ship->increaseSDs(-$change_scout_drones);
		$forces->takeSDs(-$change_scout_drones);
	}
}

if ($change_mines != 0) {
	if ($change_mines > 0) {
		$ship->decreaseMines($change_mines);
		$forces->addMines($change_mines);
		if ($ship->isCloaked()) {
			$ship->decloak();
			$player->giveTurns(1);
		}
	}
	else {
		$ship->increaseMines(-$change_mines);
		$forces->takeMines(-$change_mines);
	}
}

// message to send out
if ($forces->getOwnerID() != $player->getAccountID() && $forces->getOwner()->isForceDropMessages()) {
	$mines_message = '';
	if ($change_mines > 0)
		$mines_message = 'added ' . $change_mines . ' mine';
	elseif ($change_mines < 0)
		$mines_message = 'removed ' . abs($change_mines) . ' mine';
	//add s to mine if necesary
	if (abs($change_mines) > 1)
		$mines_message .= 's';

	if ($change_combat_drones > 0)
		$combat_drones_message = ($change_mines <= 0 ? 'added ' : '') . $change_combat_drones . ' combat drone';
	elseif ($change_combat_drones < 0)
		$combat_drones_message = ($change_mines >= 0 ? 'removed ' : '') . abs($change_combat_drones) . ' combat drone';
	//add s to drone if necesary
	if (abs($change_combat_drones) > 1)
		$combat_drones_message .= 's';

	if ($change_scout_drones > 0) {
		$scout_drones_message = '';
		if ((isset($combat_drones_message) && $change_combat_drones < 0) || (!isset($combat_drones_message) && $change_mines <= 0))
			$scout_drones_message = 'added ';
		$scout_drones_message .= $change_scout_drones . ' scout drone';
	}
	elseif ($change_scout_drones < 0) {
		$scout_drones_message = '';
		if ((isset($combat_drones_message) && $change_combat_drones > 0) || (!isset($combat_drones_message) && $change_mines >= 0))
			$scout_drones_message = 'removed ';
		$scout_drones_message .= abs($change_scout_drones) . ' scout drone';
	}
	//add s to drone if necesary
	if (abs($change_scout_drones) > 1)
		$scout_drones_message .= 's';

	// now compile it together
	$message = $player->getPlayerName() . ' has ' . $mines_message;

	if (!empty($mines_message) && isset($combat_drones_message) && !isset($scout_drones_message))
		$message .= ' and ' . $combat_drones_message;
	elseif (!empty($mines_message) && isset($combat_drones_message))
		$message .= ', ' . $combat_drones_message;
	elseif (empty($mines_message) && isset($combat_drones_message))
		$message .= $combat_drones_message;

	if (!empty($mines_message) && isset($combat_drones_message) && isset($scout_drones_message))
		$message .= ', and ' . $scout_drones_message;
	elseif ((!empty($mines_message) || isset($combat_drones_message)) && isset($scout_drones_message))
		$message .= ' and ' . $scout_drones_message;
	elseif (empty($mines_message) && !isset($combat_drones_message) && isset($scout_drones_message))
		$message .= $scout_drones_message;

	if ($change_mines >= 0 && $change_combat_drones >= 0 && $change_scout_drones >= 0)
		$message .= ' to';
	elseif ($change_mines <= 0 && $change_combat_drones <= 0 && $change_scout_drones <= 0)
		$message .= ' from';
	else
		$message .= ' from/to';

	$message .= ' your stack in sector ' . Globals::getSectorBBLink($forces->getSectorID());

	$player->sendMessage($forces->getOwnerID(), MSG_SCOUT, $message, false);
}

$account->log(LOG_TYPE_FORCES, $change_combat_drones . ' combat drones, ' . $change_scout_drones . ' scout drones, ' . $change_mines . ' mines', $player->getSectorID());

$forces->updateExpire();
$forces->update(); // Needs to be in db to show up on CS instantly when querying sector forces

// If we dropped forces from the Local Map, stay on that page
if (isset($var['referrer']) && $var['referrer'] == 'map_local.php') {
	$body = $var['referrer'];
} else {
	$body = 'current_sector.php';
}
forward(create_container('skeleton.php', $body));
