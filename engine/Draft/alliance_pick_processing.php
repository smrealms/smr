<?php
if(!is_numeric($_REQUEST['picked_account_id']))
{
	create_error('You have to pick a player');
}
$db->query('
SELECT MIN(alliance_member) min_members
FROM
(
	SELECT COUNT(*) alliance_member
	FROM player
	WHERE game_id='.$db->escapeNumber($player->getGameID()).' AND alliance_id!=302 AND alliance_id!=0
	GROUP BY alliance_id
)');
$db->nextRecord();

if($player->getAlliance()->getNumMembers()>$db->getInt('min_members'))
{
	create_error('You have to wait for others to pick first.');
}
$pickedPlayer =& SmrPlayer::getPlayer($_REQUEST['picked_account_id'], $player->getGameID());

if($pickedPlayer->hasAlliance())
{
	create_error('Picked player already has an alliance');
}
// assign the player to the current alliance
$pickedPlayer->joinAlliance($player->getAllianceID());
$pickedPlayer->update();

forward(create_container('skeleton.php', 'alliance_roster.php'));

?>