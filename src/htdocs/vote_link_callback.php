<?php declare(strict_types=1);
// Callback script for player voting on external sites

use Smr\VoteLink;
use Smr\VoteSite;

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
	$link = new VoteLink(VoteSite::from($linkId), $accountId, $gameId);
	if (!$link->setFreeTurnsAwarded()) {
		return;
	}

	// Lock the sector to ensure the player gets the turns
	// Refresh player after lock is acquired in case any values are stale
	$player = SmrPlayer::getPlayer($accountId, $gameId);
	$lock = Smr\SectorLock::getInstance();
	$lock->acquireForPlayer($player);
	$player = SmrPlayer::getPlayer($accountId, $gameId, true);

	//Give turns via added time, no rounding errors.
	$player->setLastTurnUpdate($player->getLastTurnUpdate() - VOTE_BONUS_TURNS_TIME);
	$player->save();
	$lock->release();

} catch (Throwable $e) {
	handleException($e);
}
