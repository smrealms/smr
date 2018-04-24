<?php

$container = create_container('skeleton.php', 'current_sector.php');

// Sanity check that we got here by means of allowing free turns
if ($var['can_get_turns'] == true) {
	// Turns are updated by setting the last turn update to an earlier time.
	// Make sure not to set their last turn update to before start time.
	$maxFreeTurnsTime = count(VoteSite::getAllSites()) * VOTE_BONUS_TURNS_TIME;
	$startFreeTurnsDate = $player->getGame()->getStartTurnsDate() + $maxFreeTurnsTime;
	if ($player->getLastTurnUpdate() > $startFreeTurnsDate) {
		// Allow vote
		// Don't start the timeout until the vote actually goes through.
		$db->query('REPLACE INTO vote_links (account_id, link_id, timeout, turns_claimed) VALUES(' . $db->escapeNumber($player->getAccountID()) . ',' . $db->escapeNumber($var['link_id']) . ',' . $db->escapeNumber(0) . ',' . $db->escapeBoolean(false) . ')');
		$voting = '<b><span class="red">v</span>o<span class="blue">t</span><span class="red">i</span>n<span class="blue">g</span></b>';
		$container['msg'] = "Thank you for $voting! You will receive bonus turns once your vote is processed.";
	} else {
		create_error('You cannot gain bonus turns in this game yet, please wait '.format_time($startFreeTurnsDate - min(TIME, $player->getLastTurnUpdate())).'.');
	}
}

forward($container);
