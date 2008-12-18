<?

$speed		= $var['speed'];
$cost		= $var['cost'] - $ship->cost / 2;
$race_id	= $var['race_id'];

// trade master 33
// trip maker 30
//(22,25,23,75,43,55,61,24,21,38,67,33,49)
// Top racials minus ATM + top UG/FED are restricted 

if ($account->get_rank() < FLEDGLING && $account->veteran == 'FALSE' && in_array($var['ship_id'], array(22,25,75,43,55,61,38,67,49)))
	create_error('You can\'t buy that ship while still ranked as Newbie or Beginner!');

if ($var['buyer_restriction'] == 2 && $player->getAlignment() > -100)
	create_error('You can\'t buy smuggler ships!');

if ($var['buyer_restriction'] == 1 && $player->getAlignment() < 100)
	create_error('You can\'t buy federal ships!');

if ($race_id != 1 && $player->getRaceID() != $race_id)
	create_error('You can\'t buy other race\'s ships!');

/*if ($player->getAccountID() == 101)
	create_error('Cheaters do NOT get ships!');*/

// do we have enough cash?
if ($player->getCredits() < $cost)
	create_error('You do not have enough cash to purchase this ship!');

// adapt turns
$player->setTurns($player->getTurns() * $speed / $ship->speed);

// take the money from the user
$player->decreaseCredits($cost);
$player->update();

// assign the new ship
$ship->setShipTypeID($var['ship_id']);

// delete cargo
$ship->remove_all_cargo();

// update
$ship->update();

// get new ship object
$ship =& SmrShip::getShip(SmrSession::$game_id,SmrSession::$account_id);

// adapt hardware
$db->query('SELECT * FROM hardware_type');
while ($db->next_record()) {

	$hardware_type_id = $db->f('hardware_type_id');

	// take hardware we don't support
	if ($ship->getHardware($hardware_type_id) > $ship->getMaxHardware($hardware_type_id))
		$ship->setHardware($hardware_type_id,$ship->getMaxHardware($hardware_type_id));
}

// take weapons that we can't carry
$ship->weapon = array_slice($ship->weapon, 0, $ship->getHardpoints());

// disable hardware
$db->query('DELETE FROM ship_is_cloaked WHERE account_id = '.$player->getAccountID().' AND ' .
											 'game_id = '.$player->getGameID());
$db->query('DELETE FROM ship_has_illusion WHERE account_id = '.$player->getAccountID().' AND ' .
											   'game_id = '.$player->getGameID());

// if we changed max hardware we need to compensate this.
$ship->mark_seen();

// update again
$ship->update_hardware();
$ship->update_weapon();
$ship->mark_seen();

$account->log(10, 'Buys a '.$ship->ship_name.' for '.$cost.' credits', $player->getSectorID());

forward(create_container('skeleton.php', 'current_sector.php'));

?>
