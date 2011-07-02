<?php
require_once(get_file_loc('SmrAlliance.class.inc'));
$alliance =& SmrAlliance::getAlliance($player->getAllianceID(), SmrSession::$game_id);
$accountIDs = $_REQUEST['account_id'];

if(empty($accountIDs))
{
	create_error('You have to choose someone to remove them!');
}

foreach ($accountIDs as $accountID)
{
	if ($accountID == $alliance->getLeaderID())
		create_error('You can\'t remove the leader!');
}

foreach ($accountIDs as $accountID)
{
	SmrPlayer::getPlayer($accountID, $player->getGameID())->leaveAlliance($player);
}

forward(create_container('skeleton.php', 'alliance_roster.php'));

?>