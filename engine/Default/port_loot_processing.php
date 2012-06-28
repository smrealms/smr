<?php
// get good name, id, ...
$good_id = $var['GoodID'];
$good_name = Globals::getGoodName($good_id);
$amount = $_REQUEST['amount'];
if (!is_numeric($amount))
	create_error('Numbers only please!');
$amount = floor($amount);
if ($amount <= 0)
	create_error('You must enter an amount > 0!');

$port =& $player->getSectorPort();
$good =& $port->getGood($good_id);
// check if there are enough left at port
if ($good['Amount'] < $amount)
   create_error('There isnt that much to loot.');

// check if we have enough room for the thing we are going to buy
if ($good['TransactionType'] == 'Buy' && $amount > $ship->getEmptyHolds())
   create_error('Scanning your ships indicates that you do not have enough free cargo bays!');

// do we have enough turns?
if ($player->getTurns() == 0)
   create_error('You don\'t have enough turns to loot.');

$account->log(LOG_TYPE_TRADING, 'Player Loots '.$amount.' '.$good_name, $player->getSectorID());
$container = array();
$container['url'] = 'skeleton.php';
$container['body'] = 'port_loot.php';
$ship->increaseCargo($good_id,$amount);
$ship->updateCargo();
$port->decreaseGoodAmount($good_id,$amount);
$port->update();
forward($container);

?>