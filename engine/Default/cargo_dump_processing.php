<?php
$good_id = $var['good_id'];
$good_name = Globals::getGoodName($good_id);
if (isset($_REQUEST['amount'])) {
	SmrSession::updateVar('amount', $_REQUEST['amount']);
}
$amount = $var['amount'];
if (!is_numeric($amount)) {
	create_error('Numbers only please!');
}

if ($amount <= 0) {
	create_error('You must actually enter an amount > 0!');
}

if ($player->isLandedOnPlanet()) {
	create_error('You can\'t dump cargo while on a planet!');
}

if ($player->getTurns() < 1) {
	create_error('You do not have enough turns to dump cargo!');
}

//lets make sure there is actually that much on the ship
if ($amount > $ship->getCargo($good_id)) {
	create_error('You can\'t dump more than you have.');
}

if ($sector->offersFederalProtection()) {
	create_error('You can\'t dump cargo in a Federal Sector!');
}

$container = create_container('skeleton.php', 'current_sector.php');

if ($player->getExperience() > 0) {
	// If they have any experience left, lose exp

	// get the distance
	$x = Globals::getGood($good_id);
	$x['TransactionType'] = 'Sell';
	$good_distance = Plotter::findDistanceToX($x, $sector, true);
	if (is_object($good_distance)) {
		$good_distance = $good_distance->getRelativeDistance();
	}
	$good_distance = max(1, $good_distance);

	// Don't lose more exp than you have
	$lost_xp = min($player->getExperience(),
	               round(SmrPort::getBaseExperience($amount, $good_distance)));
	$player->decreaseExperience($lost_xp);
	$player->increaseHOF($lost_xp, array('Trade', 'Experience', 'Jettisoned'), HOF_PUBLIC);

	$container['msg'] = 'You have jettisoned <span class="yellow">' . $amount . '</span> ' . pluralise('unit', $amount) . ' of ' . $good_name . ' and have lost <span class="exp">' . $lost_xp . '</span> experience.';
	// log action
	$account->log(LOG_TYPE_TRADING, 'Dumps ' . $amount . ' of ' . $good_name . ' and loses ' . $lost_xp . ' experience', $player->getSectorID());
} else {
	// No experience to lose, so damage the ship
	$damage = ceil($amount / 5);

	// Don't allow ship to be destroyed dumping cargo
	if ($ship->getArmour() <= $damage) {
		create_error('Your ship is too damaged to risk dumping cargo!');
	}

	$ship->decreaseArmour($damage);
	$ship->removeUnderAttack(); // don't trigger attack warning

	$container['msg'] = 'You have jettisoned <span class="yellow">' . $amount . '</span> ' . pluralise('unit', $amount) . ' of ' . $good_name . '. Due to your lack of piloting experience, the cargo pierces the hull of your ship as you clumsily try to jettison the goods through the bay doors, destroying <span class="red">' . $damage . '</span> ' . pluralise('plate', $damage) . ' of armour!';
	// log action
	$account->log(LOG_TYPE_TRADING, 'Dumps ' . $amount . ' of ' . $good_name . ' and takes ' . $damage . ' armour damage', $player->getSectorID());
}

// take turn
$player->takeTurns(1, 1);

$ship->decreaseCargo($good_id, $amount);
$player->increaseHOF($amount, array('Trade', 'Goods', 'Jettisoned'), HOF_ALLIANCE);

forward($container);
