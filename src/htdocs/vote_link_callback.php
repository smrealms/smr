<?php declare(strict_types=1);

// Callback script for player voting on external sites

use Smr\Request;
use Smr\VoteLink;
use Smr\VoteSite;

try {
	require_once('../bootstrap.php');

	if (Request::has('account') && Request::has('link')) {
		// callback from TWG
		$accountId = Request::getInt('account');
		$linkId = Request::getInt('link');
	} else {
		exit;
	}

	$link = new VoteLink(VoteSite::from($linkId), $accountId);
	$link->awardFreeTurns();

} catch (Throwable $e) {
	handleException($e);
}
