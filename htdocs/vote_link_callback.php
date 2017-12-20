<?php
// Callback script for player voting on external sites

if (isset($_POST['account']) && isset($_POST['game']) && isset($_POST['link'])) {
	// callback from TWG
	$accountId = $_POST['account'];
	$gameId = $_POST['game'];
	$linkId = $_POST['link'];
} else if (isset($_GET['votedef'])) {
	// callback from DOG
	$data = explode(',', $_GET['votedef']);
	$accountId = $data[0];
	$gameId = $data[1];
	$linkId = $data[2];
} else {
	exit;
}

require_once('config.inc');
require_once(ENGINE . 'Default/smr.inc');

// Is the player allowed to get free turns from this link right now?
// If player clicked a valid free turns link, they have `can_get_turns=true`
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
	$player->setLastTurnUpdate($player->getLastTurnUpdate()-VOTE_BONUS_TURNS_TIME); //Give turns via added time, no rounding errors.
	$player->save();
	release_lock();

	// Prevent getting additional turns until a valid free turns link is clicked again
	$db->query('UPDATE vote_links SET turns_claimed=' . $db->escapeBoolean(true) . ' WHERE account_id=' . $db->escapeNumber($accountId) . ' AND link_id=' . $db->escapeNumber($linkId));
}

?>
