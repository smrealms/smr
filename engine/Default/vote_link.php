<?php
$db->query('SELECT timeout FROM vote_links WHERE account_id=' . $db->escapeNumber($player->getAccountID()) . ' AND link_id=' . $db->escapeNumber($var['link_id']) . ' LIMIT 1');

// They get to vote once every 24 hours
$valid = !$db->nextRecord() || $db->getField('timeout') <= TIME - TIME_BETWEEN_VOTING;

// Sanity checking
if($var['link_id'] > 3 || $var['link_id'] < 1 ) {
	$valid = false;
}


if($valid == true) {
	if($player->getLastTurnUpdate() > $player->getGame()->getStartTurnsDate() + VOTE_BONUS_TURNS_TIME) { //Make sure we cannot take their last turn update before start time
		// Allow vote
		$db->query('REPLACE INTO vote_links (account_id,link_id,timeout) VALUES(' . $db->escapeNumber($player->getAccountID()) . ',' . $db->escapeNumber($var['link_id']) . ',' . $db->escapeNumber(TIME) . ')');
		$player->setLastTurnUpdate($player->getLastTurnUpdate()-VOTE_BONUS_TURNS_TIME); //Give turns via added time, no rounding errors.
		$player->updateTurns(); //Display updated turns straight away.
	}
	else {
		create_error('You cannot gain bonus turns in this game yet, please wait '.format_time( $player->getGame()->getStartTurnsDate() + VOTE_BONUS_TURNS_TIME - min(TIME, $player->getLastTurnUpdate())).'.');
	}
}

$container = create_container('skeleton.php', 'current_sector.php');
$container['voted'] = true;
forward($container);

?>
