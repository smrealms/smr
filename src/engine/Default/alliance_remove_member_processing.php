<?php declare(strict_types=1);

use Smr\Request;

$session = Smr\Session::getInstance();
$player = $session->getPlayer();

$accountIDs = Request::getIntArray('account_id', []);

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
	$currPlayer->update(); // we need better locking here
}

Page::create('alliance_roster.php')->go();
