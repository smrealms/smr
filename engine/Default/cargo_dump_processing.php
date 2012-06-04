<?php
require_once(get_file_loc('SmrSector.class.inc'));

$good_id = $var['good_id'];
$good_name = Globals::getGoodName($good_id);
if(isset($_REQUEST['amount'])) SmrSession::updateVar('amount',$_REQUEST['amount']);
$amount = $var['amount'];
if (!is_numeric($amount))
	create_error('Numbers only please');

if ($amount <= 0)
	create_error('You must actually enter an ammount > 0!');

if ($player->isLandedOnPlanet())
	create_error('You can\'t dump cargo while on a planet!');

if ($player->getTurns() < 1)
	create_error('You do not have enough turns to dump cargo!');

//lets make sure there is actually that much on the ship
if ($amount > $ship->getCargo($good_id))
	create_error('You can\'t dump more than you have.');

$sector =& SmrSector::getSector(SmrSession::$game_id, $player->getSectorID());
if ($sector->has_fed_beacon())
	create_error('You can\'t dump cargo in a Federal Sector!');

require_once('shop_goods.inc');

// get the distance
$x = Globals::getGood($good_id);
$x['TransactionType'] = 'Sell';
$good_distance = Plotter::findDistanceToX($x, $sector, true);
if(is_object($good_distance))
	$good_distance = $good_distance->getRelativeDistance();
$good_distance = max(1,$good_distance);

$lost_xp = (round($amount / 30) + 1) * 2 * $good_distance;
$player->decreaseExperience($lost_xp);
$player->increaseHOF($lost_xp,array('Trade','Experience', 'Jettisoned'), HOF_PUBLIC);

// take turn
$player->takeTurns(1,1);

$ship->decreaseCargo($good_id,$amount);
$player->increaseHOF($amount,array('Trade','Goods', 'Jettisoned'), HOF_PUBLIC);

// log action
$account->log(6, 'Dumps '.$amount.' of '.$good_name.' and looses '.$lost_xp.' experience', $player->getSectorID());

$container = array();
$container['url'] = 'skeleton.php';
if ($amount > 1)
	$container['msg'] = 'You have jettisoned <span class="yellow">'.$amount.'</span> units of '.$good_name.' and have lost <span class="exp">'.$lost_xp.'</span> experience.';
else
	$container['msg'] = 'You have jettisoned <span class="yellow">'.$amount.'</span> unit of '.$good_name.' and have lost <span class="exp">'.$lost_xp.'</span> experience.';

if ($player->isLandedOnPlanet())
	$container['body'] = 'planet_main.php';
else
	$container['body'] = 'current_sector.php';

forward($container);

?>