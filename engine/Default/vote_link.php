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


if($valid == true)
{
	if($player->getLastTurnUpdate() > $player->getGame()->getStartTurnsDate() + VOTE_BONUS_TURNS_TIME) //Make sure we cannot take their last turn update before start time
	{
		// Allow vote
		$db->query('REPLACE INTO vote_links (account_id,link_id,timeout) VALUES(' . SmrSession::$account_id . ',' . $var['link_id'] . ',' . TIME . ')');
		$player->setLastTurnUpdate($player->getLastTurnUpdate()-VOTE_BONUS_TURNS_TIME); //Give turns via added time, no rounding errors.
		$player->updateTurns(); //Display updated turns straight away.
	}
	else
	{
		create_error('You cannot gain bonus turns in this game yet, please wait '.format_time( $player->getGame()->getStartTurnsDate() + VOTE_BONUS_TURNS_TIME - min(TIME, $player->getLastTurnUpdate())).'.');
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
