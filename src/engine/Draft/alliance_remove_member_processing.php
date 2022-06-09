<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$player = $session->getPlayer();

$accountIDs = Smr\Request::getIntArray('account_id', []);

if (empty($accountIDs)) {
	create_error('You have to choose someone to remove them!');
}

if (in_array($player->getAlliance()->getLeaderID(), $accountIDs)) {
	create_error('You can\'t kick the leader!');
}

if (in_array($player->getAccountID(), $accountIDs)) {
	create_error('You can\'t kick yourself!');
}

foreach ($accountIDs as $accountID) {
	$currPlayer = SmrPlayer::getPlayer($accountID, $player->getGameID());
	if (!$player->sameAlliance($currPlayer)) {
		throw new Exception('Cannot kick someone from another alliance!');
	}
	$currPlayer->leaveAlliance($player);
	$currPlayer->setSectorID(1);
	$currPlayer->setNewbieTurns(max(1, $currPlayer->getNewbieTurns()));
	$currPlayer->setLandedOnPlanet(false);
}

Page::create('alliance_roster.php')->go();
