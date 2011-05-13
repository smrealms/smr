<?php
if (!$player->isLandedOnPlanet())
	create_error('You are not on a planet!');
require_once(get_file_loc('SmrPlanet.class.inc'));
$amount = trim($_REQUEST['amount']);
if (!is_numeric($amount))
	create_error('Numbers only please');
    
// only whole numbers allowed
$amount = round($amount);

if ($amount <= 0)
    create_error('You must actually enter an amount > 0!');
if ($player->getNewbieTurns() > 0)
	create_error('You can\'t drop defenses under newbie protection!');
// get a planet from the sector where the player is in
$planet =& SmrPlanet::getPlanet(SmrSession::$game_id,$player->getSectorID());

$type_id = $var['type_id'];
$action = $_REQUEST['action'];
// transfer to ship
if ($action == 'Ship')
{
	include(get_file_loc('planet_defenses_disallow.php'));
    // do the user wants to transfer shields?
    if ($type_id == 1) {

        // do we want transfer more than we have?
        if ($amount > $planet->getShields())
            create_error('You can\'t take more shields from planet than are on it!');

        // do we want to transfer more than we can carry?
        if ($amount > $ship->getMaxShields() - $ship->getShields())
            create_error('You can\'t take more shields than you can carry!');

        // now transfer
        $planet->decreaseShields($amount);
        $ship->increaseShields($amount);
        $account->log(11, 'Player takes '.$amount.' shields from planet.', $player->getSectorID());

    // do the user wants to transfer drones?
    }
    else if ($type_id == 4)
    {
        // do we want transfer more than we have?
        if ($amount > $planet->getCDs())
            create_error('You can\'t take more drones from planet than are on it!');

        // do we want to transfer more than we can carry?
        if ($amount > $ship->getMaxCDs() - $ship->getCDs())
            create_error('You can\'t take more drones than you can carry!');

        // now transfer
        $planet->decreaseCDs($amount);
        $ship->increaseCDs($amount);
        $account->log(11, 'Player takes '.$amount.' drones from planet.', $player->getSectorID());
    }
}
elseif ($action == 'Planet')
{
	include(get_file_loc('planet_defenses_disallow.php'));
    // do the user wants to transfer shields?
    if ($type_id == 1)
    {
        // do we want transfer more than we have?
        if ($amount > $ship->getShields())
            create_error('You can\'t transfer more shields than you carry!');

        // do we want to transfer more than the planet can hold?
        if ($amount + $planet->getShields() > $planet->getMaxShields())
            create_error('The planet can\'t hold more than ' . $planet->getMaxShields() . ' shields!');

        // now transfer
        $planet->increaseShields($amount);
        $ship->decreaseShields($amount);
		$account->log(11, 'Player puts '.$amount.' shields on planet.', $player->getSectorID());
    // do the user wants to transfer drones?
    }
    else if ($type_id == 4)
    {
        // do we want transfer more than we have?
        if ($amount > $ship->getCDs())
            create_error('You can\'t transfer more combat drones than you carry!');

        // do we want to transfer more than we can carry?
        if ($amount + $planet->getCDs() > $planet->getMaxCDs())
            create_error('The planet can\'t hold more than ' . $planet->getMaxCDs() . ' drones!');

        // now transfer
        $planet->increaseCDs($amount);
        $ship->decreaseCDs($amount);
        $account->log(11, 'Player puts '.$amount.' drones on planet.', $player->getSectorID());
    }
}
else
    create_error('You must choose if you want to transfer to planet or to the ship!');

$ship->removeUnderAttack();

forward(create_container('skeleton.php', 'planet_defense.php'));

?>