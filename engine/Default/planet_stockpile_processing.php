<?php
if (!$player->isLandedOnPlanet())
	create_error('You are not on a planet!');
$amount = $_REQUEST['amount'];
if (!is_numeric($amount))
	create_error('Numbers only please');
    
$amount = floor($amount);

if ($amount <= 0)
	create_error('You must actually enter an amount > 0!');

// get a planet from the sector where the player is in
$planet =& $player->getSectorPlanet();
$action = $_REQUEST['action'];
// transfer to ship
if ($action == 'Ship') {

	// do we want transfer more than we have?
	if ($amount > $planet->getStockpile($var['good_id']))
		create_error('You can\'t take more than on planet!');

	// do we want to transfer more than we can carry?
	if ($amount > $ship->getEmptyHolds())
		create_error('You can\'t take more than you can carry!');

	// now transfer
	$planet->decreaseStockpile($var['good_id'],$amount);
	$ship->increaseCargo($var['good_id'],$amount);
	$db->query('SELECT * FROM good WHERE good_id = '.$var['good_id']);
	$db->nextRecord();
	$good_name = $db->getField('good_name');
	$account->log(11, 'Player takes '.$amount.' '.$good_name.' from planet.', $player->getSectorID());

// transfer to planet
}
elseif ($action == 'Planet')
{
	// do we want transfer more than we have?
	if ($amount > $ship->getCargo($var['good_id']))
		create_error('You can\'t store more than you carry!');

	// do we want to transfer more than the planet can hold?
	if ($amount > $planet->stockpile_left($var['good_id']))
		create_error('You can only put 600 per item at planet!');

	// now transfer
	$planet->increaseStockpile($var['good_id'],$amount);
	$ship->decreaseCargo($var['good_id'],$amount);
}

// update both
$planet->update();
$ship->update_cargo();

forward(create_container('skeleton.php', 'planet_stockpile.php'));

?>