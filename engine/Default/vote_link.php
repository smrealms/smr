<?php
$db->query('SELECT timeout FROM vote_links WHERE account_id=' . SmrSession::$account_id . ' AND link_id=' . $var['link_id'] . ' LIMIT 1');

// They get to vote once every 24 hours
if(!$db->nextRecord()) {
	$valid=true;
}
else if($db->getField('timeout') <= TIME - 86400) {
	$valid = true;
}
else {
	$valid = false;
}

// Sanity checking
if($var['link_id'] > 3 || $var['link_id'] < 1 ) {
	$valid = false;
}


if($valid == true) {
	// Allow vote
	$db->query('REPLACE INTO vote_links (account_id,link_id,timeout) VALUES(' . SmrSession::$account_id . ',' . $var['link_id'] . ',' . TIME . ')');
	if(TIME >= $player->getGame()->getStartTurnsDate())
	{
		// They get 1/3 of their hourly turns for a valid click
		$player->setLastTurnUpdate($player->getLastTurnUpdate()-1200); //Give 20mins worth of turns, no rounding errors.
		$player->updateTurns(); //Display updated turns straight away.
	}
}

$container = array();
$container['url'] = 'skeleton.php';

// Return them to the appropriate screen
if (!$player->isLandedOnPlanet())
	$container['body'] = 'current_sector.php';
else
	$container['body'] = 'planet_main.php';

$container['voted'] = true;
forward($container);

?>
