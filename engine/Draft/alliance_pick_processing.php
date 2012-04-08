<?php
if(!is_numeric($var['PickedAccountID'])) {
	create_error('You have to pick a player.');
}
$db->query('
SELECT MIN(alliance_member) min_members
FROM
(
	SELECT COUNT(*) alliance_member
	FROM player
	WHERE game_id='.$db->escapeNumber($player->getGameID()).' AND alliance_id!='.$db->escapeNumber(NHA_ID).' AND alliance_id!=0
	GROUP BY alliance_id
) t');
$db->nextRecord();

if($player->getAlliance()->getNumMembers()>$db->getInt('min_members')) {
	create_error('You have to wait for others to pick first.');
}
$pickedPlayer =& SmrPlayer::getPlayer($var['PickedAccountID'], $player->getGameID());

if($pickedPlayer->hasAlliance()) {
	if($pickedPlayer->getAllianceID()==NHA_ID) {
		$pickedPlayer->leaveAlliance();
	}
	else {
		create_error('Picked player already has an alliance.');
	}
}
// assign the player to the current alliance
$pickedPlayer->joinAlliance($player->getAllianceID());
$pickedPlayer->update();

forward(create_container('skeleton.php', 'alliance_roster.php'));

?>