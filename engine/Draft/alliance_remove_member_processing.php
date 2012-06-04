<?php
require_once(get_file_loc('SmrAlliance.class.inc'));
$alliance =& SmrAlliance::getAlliance($player->getAllianceID(), SmrSession::$game_id);
$accountIDs = $_REQUEST['account_id'];

if(empty($account_id))
{
	create_error('You have to choose someone to remove them!');
}

foreach ($account_id as $id)
{
	if ($id == $alliance->getLeaderID())
		create_error('You can\'t remove the leader!');
}

foreach ($account_id as $id)
{
	$currPlayer =& SmrPlayer::getPlayer($id, $player->getGameID());
	$currPlayer->leaveAlliance($player);
	$currPlayer->setSectorID(1);
	$currPlayer->setNewbieTurns(max(1,$currPlayer->getNewbieTurns()));
}

forward(create_container('skeleton.php', 'alliance_roster.php'));

?>