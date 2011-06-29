<?php
require_once(get_file_loc('smr_alliance.inc'));
$alliance = new SMR_ALLIANCE($player->getAllianceID(), SmrSession::$game_id);
$accountIDs = $_REQUEST['account_id'];

if(empty($account_id))
{
	create_error('You have to choose someone to remove them!');
}

foreach ($account_id as $id)
{
	if ($id == $alliance->leader_id)
		create_error('You can\'t remove the leader!');
}

foreach ($account_id as $id)
{
	SmrPlayer::getPlayer($id, $player->getGameID())->leaveAlliance($player);
}

forward(create_container('skeleton.php', 'alliance_roster.php'));

?>