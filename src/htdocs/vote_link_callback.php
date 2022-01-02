<?php declare(strict_types=1);
// Callback script for player voting on external sites

try {
	require_once('../bootstrap.php');

	if (Smr\Request::has('account') && Smr\Request::has('game') && Smr\Request::has('link')) {
		// callback from TWG
		$accountId = Smr\Request::getInt('account');
		$gameId = Smr\Request::getInt('game');
		$linkId = Smr\Request::getInt('link');
	} elseif (Smr\Request::has('votedef')) {
		// callback from DOG
		$data = explode(',', Smr\Request::get('votedef'));
		$accountId = (int)$data[0];
		$gameId = (int)$data[1];
		$linkId = (int)$data[2];
	} else {
		exit;
	}

	// Is the player allowed to get free turns from this link right now?
	// Check if player clicked a valid free turns link.
	$voteSite = Smr\VoteSite::getSite($linkId, $accountId);
	if (!$voteSite->isLinkClicked()) {
		return;
	}

	// Eligibility was checked in vote_site.php.
	// So give free turns now!
	$player = SmrPlayer::getPlayer($accountId, $gameId);

	// Lock the sector to ensure the player gets the turns
	// Refresh player after lock is acquired in case any values are stale
	acquire_lock($player->getSectorID());
	$player = SmrPlayer::getPlayer($accountId, $gameId, true);

	// Now that we are locked, check the database again to make sure turns
	// weren't claimed while we were waiting for the lock.
	// This prevents players from spamming the callback for lots of free turns.
	if (!$voteSite->isLinkClicked()) {
		throw new Exception('Account ID ' . $accountId . ' attempted vote link abuse');
	}

	// Prevent getting additional turns until a valid free turns link is clicked again
	$voteSite->setFreeTurnsAwarded();

	//Give turns via added time, no rounding errors.
	$player->setLastTurnUpdate($player->getLastTurnUpdate() - VOTE_BONUS_TURNS_TIME);
	$player->save();
	release_lock();

} catch (Throwable $e) {
	handleException($e);
}
