<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();
$ship = $player->getShip();

// get good name, id, ...
$good_id = $var['GoodID'];
$good_name = Globals::getGoodName($good_id);
$amount = Request::getInt('amount');
if ($amount <= 0) {
	create_error('You must enter an amount > 0!');
}

$port = $player->getSectorPort();
$good = $port->getGood($good_id);
// check if there are enough left at port
if ($good['Amount'] < $amount) {
   create_error('There isnt that much to loot.');
}

// check if we have enough room for the thing we are going to buy
if ($good['TransactionType'] === TRADER_BUYS && $amount > $ship->getEmptyHolds()) {
   create_error('Scanning your ships indicates that you do not have enough free cargo bays!');
}

// do we have enough turns?
if ($player->getTurns() == 0) {
   create_error('You don\'t have enough turns to loot.');
}

$player->log(LOG_TYPE_TRADING, 'Player Loots ' . $amount . ' ' . $good_name);
$ship->increaseCargo($good_id, $amount);
$port->decreaseGoodAmount($good_id, $amount);

$container = Page::create('skeleton.php', 'port_loot.php');
$container->go();
