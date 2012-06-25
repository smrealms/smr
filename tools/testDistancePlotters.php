<?php

require_once('/home/page/SMR/htdocs/config.inc');
require_once(ENGINE . 'Default/smr.inc');
require_once(get_file_loc('SmrSector.class.inc'));
require_once(get_file_loc('SmrGalaxy.class.inc'));
require_once(get_file_loc('Plotter.class.inc'));

testDistances(1);

function testDistances($gameID)
{
	//Initialise cache for fairness
	$galaxies =& SmrGalaxy::getGameGalaxies($gameID);
	$galaxySectors = array();
	foreach($galaxies as &$galaxy)
	{
		$galaxiesSectors[] =& $galaxy->getSectors();
	} unset($galaxy);
	foreach($galaxiesSectors as &$galaxySectors)
	{
		foreach($galaxySectors as &$galaxySector)
		{
			if($galaxySector->hasPort())
				$galaxySector->getPort()->getGoods();
		} unset($galaxySector);
	} unset($galaxySectors);
	//Test plotters
	$newTime = 0;
	$oldTime = 0;
	foreach($galaxiesSectors as &$galaxySectors)
	{
		foreach($galaxySectors as &$galaxySector)
		{
			if($galaxySector->hasPort())
			{
				$goods =& $galaxySector->getPort()->getGoods();
				foreach($goods as &$good)
				{
					$time = microtime(true);
					$newDI = getGoodDistanceNew($galaxySector,$good['ID'],$good['TransactionType']);
					$newTime += microtime(true) - $time;
					
					$time = microtime(true);
					$oldDI = getGoodDistanceOld($galaxySector,$good['ID'],$good['TransactionType']);
					$oldTime += microtime(true) - $time;
					
					if($newDI!=$oldDI)
					{
						echo 'Difference, new: '.$newDI.', old:'.$oldDI.', sector:'.$galaxySector->getSectorID().', good:'.$good['ID'].EOL;
					}
				} unset($good);
			}
		} unset($galaxySector);
	} unset($galaxySectors);
	echo 'New time: '.$newTime.', old time:'.$oldTime.EOL;
}


function getGoodDistanceNew(SmrSector &$sector, $goodID, $transaction)
{
	global $var, $container;

	// check if we already did this
	if (isset($var['good_distance']))
	{
		// transfer this value
		transfer('good_distance');

		// return this value
		return $var['good_distance'];
	}
	$x = Globals::getGood($goodID);
	switch($transaction)
	{
		case 'Buy':
			$x['TransactionType'] = 'Sell';
		break;
		case 'Sell':
			$x['TransactionType'] = 'Buy';
	}
	$di = Plotter::findDistanceToX($x, $sector, true);
	if(is_object($di))
		$di = $di->getRelativeDistance();
	$container['good_distance'] = $di;
	return $di;
}

function getGoodDistanceOld(SmrSector &$sector, $goodID, $transaction)
{
	// if we buy a good we're looking for the nearest sector that sells that good
	if ($transaction == 'Buy')
		$neg_transaction = 'Sell';
	elseif ($transaction == 'Sell')
		$neg_transaction = 'Buy';

	// initialize the queue. all sectors we have to visit are in here
	$sector_queue = array();

	// keeps the distance to the start sector
	$sector_distance = array();

	// putting start sector in queue
	array_push($sector_queue, $sector->getSectorID());

	// it has a null distance
	$sector_distance[$sector->getSectorID()] = 0;

	$good_distance = 0;
	while (sizeof($sector_queue) > 0) {

		// get current sector and
		$curr_sector_id = array_shift($sector_queue);

		// get the distance for this sector from the start sector
		$distance = $sector_distance[$curr_sector_id];

		// create a new sector object
		$curr_sector =& SmrSector::getSector($sector->getGameID(), $curr_sector_id);

		// does the current sector buy/sell the good we're looking for?
        if ($good_distance != 0) {
			if ($curr_sector->hasPort() && $curr_sector->getPort()->hasGood($goodID, $neg_transaction) && $distance < $good_distance)
				$good_distance = $distance;
        } else {
			if ($curr_sector->hasPort() && $curr_sector->getPort()->hasGood($goodID, $neg_transaction))
				$good_distance = $distance;
        }

		// if we already found a port that buy or sell our product we don't need
		// to go further than this one.
		if ($good_distance != 0 && $good_distance <= $distance) continue;

		// enqueue all neighbours
		if ($curr_sector->getLinkUp() > 0 && (!isset($sector_distance[$curr_sector->getLinkUp()]) || $sector_distance[$curr_sector->getLinkUp()] > $distance + 1)) {

			array_push($sector_queue, $curr_sector->getLinkUp());
			$sector_distance[$curr_sector->getLinkUp()] = $distance + 1;

		}

		if ($curr_sector->getLinkDown() > 0 && (!isset($sector_distance[$curr_sector->getLinkDown()]) || $sector_distance[$curr_sector->getLinkDown()] > $distance + 1)) {

			array_push($sector_queue, $curr_sector->getLinkDown());
			$sector_distance[$curr_sector->getLinkDown()] = $distance + 1;

		}

		if ($curr_sector->getLinkLeft() > 0 && (!isset($sector_distance[$curr_sector->getLinkLeft()]) || $sector_distance[$curr_sector->getLinkLeft()] > $distance + 1)) {

			array_push($sector_queue, $curr_sector->getLinkLeft());
			$sector_distance[$curr_sector->getLinkLeft()] = $distance + 1;

		}

		if ($curr_sector->getLinkRight() > 0 && (!isset($sector_distance[$curr_sector->getLinkRight()]) || $sector_distance[$curr_sector->getLinkRight()] > $distance + 1)) {

			array_push($sector_queue, $curr_sector->getLinkRight());
			$sector_distance[$curr_sector->getLinkRight()] = $distance + 1;

		}

		if ($curr_sector->getWarp() > 0 && (!isset($sector_distance[$curr_sector->getWarp()]) || $sector_distance[$curr_sector->getWarp()] > $distance + 5)) {

			array_push($sector_queue, $curr_sector->getWarp());
			$sector_distance[$curr_sector->getWarp()] = $distance + 5;

		}

	}

	$container['good_distance'] = $good_distance;

	return $good_distance;
}
?>
