<?php declare(strict_types=1);
$playerIDs = Request::getIntArray('player_id', []);

if (empty($playerIDs)) {
	create_error('You have to choose someone to remove them!');
}

if (in_array($player->getAlliance()->getLeaderPlayerID(), $playerIDs)) {
	create_error('You can\'t kick the leader!');
}

if (in_array($player->getPlayerID(), $playerIDs)) {
	create_error('You can\'t kick yourself!');
}

foreach ($playerIDs as $playerID) {
	$currPlayer = SmrPlayer::getPlayer($playerID, $player->getGameID());
	if (!$player->sameAlliance($currPlayer)) {
		throw new Exception('Cannot kick someone from another alliance!');
	}
	$currPlayer->leaveAlliance($player);
}

forward(create_container('skeleton.php', 'alliance_roster.php'));
