<?php declare(strict_types=1);
// Callback script for player voting on external sites

if (Request::has('account') && Request::has('game') && Request::has('link')) {
	// callback from TWG
	$accountId = Request::getInt('account');
	$gameId = Request::getInt('game');
	$linkId = Request::getInt('link');
} elseif (Request::has('votedef')) {
	// callback from DOG
	$data = explode(',', Request::get('votedef'));
	$accountId = (int)$data[0];
	$gameId = (int)$data[1];
	$linkId = (int)$data[2];
} else {
	exit;
}

require_once('config.inc');
require_once(LIB . 'Default/smr.inc');

// Is the player allowed to get free turns from this link right now?
// If player clicked a valid free turns link, they have `turns_claimed=false`
$db = new SmrMySqlDatabase();
$db->query('SELECT timeout FROM vote_links WHERE account_id=' . $db->escapeNumber($accountId) . ' AND link_id=' . $db->escapeNumber($linkId) . ' AND turns_claimed=' . $db->escapeBoolean(false) . ' LIMIT 1');

if ($db->nextRecord()) {
	// Eligibility was checked when `turns_claimed` was set to false.
	// So give free turns now!
	$player = SmrPlayer::getPlayer($accountId, $gameId);

	// Lock the sector to ensure the player gets the turns
	// Refresh player after lock is acquired in case any values are stale
	acquire_lock($player->getSectorID());
	SmrPlayer::refreshCache();

	// Now that we are locked, check the database again to make sure turns
	// weren't claimed while we were waiting for the lock.
	// This prevents players from manually spamming the callback for lots of free turns.
	$db->query('SELECT timeout FROM vote_links WHERE account_id=' . $db->escapeNumber($accountId) . ' AND link_id=' . $db->escapeNumber($linkId) . ' AND turns_claimed=' . $db->escapeBoolean(false) . ' LIMIT 1');
	if (!$db->nextRecord()) {
		exit;
	}

	// Prevent getting additional turns until a valid free turns link is clicked again
	$db->query('REPLACE INTO vote_links (account_id, link_id, timeout, turns_claimed) VALUES(' . $db->escapeNumber($accountId) . ',' . $db->escapeNumber($linkId) . ',' . $db->escapeNumber(TIME) . ',' . $db->escapeBoolean(true) . ')');

	$player->setLastTurnUpdate($player->getLastTurnUpdate() - VOTE_BONUS_TURNS_TIME); //Give turns via added time, no rounding errors.
	$player->save();
	release_lock();
}
