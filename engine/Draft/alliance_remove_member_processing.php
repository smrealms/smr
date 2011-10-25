<?php
$accountIDs = $_REQUEST['account_id'];

if(empty($accountIDs))
	create_error('You have to choose someone to remove them!');

if(in_array($player->getAlliance()->getLeaderID(), $accountIDs))
	create_error('You can\'t remove the leader!');

foreach ($accountIDs as $accountID)
{
	$currPlayer =& SmrPlayer::getPlayer($accountID, $player->getGameID());
	$currPlayer->leaveAlliance($player);
	$currPlayer->setSectorID(1);
	$currPlayer->setNewbieTurns(max(1,$currPlayer->getNewbieTurns()));
}

forward(create_container('skeleton.php', 'alliance_roster.php'));

?>