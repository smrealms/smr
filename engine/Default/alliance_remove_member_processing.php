<?php
$accountIDs = $_REQUEST['account_id'];

if(empty($accountIDs))
	create_error('You have to choose someone to remove them!');

if(in_array($player->getAlliance()->getLeaderID(), $accountIDs))
	create_error('You can\'t remove the leader!');

foreach ($accountIDs as $accountID)
{
	SmrPlayer::getPlayer($accountID, $player->getGameID())->leaveAlliance($player);
}

forward(create_container('skeleton.php', 'alliance_roster.php'));

?>