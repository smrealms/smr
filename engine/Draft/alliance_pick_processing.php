<?php
if(!is_numeric($var['PickedAccountID'])) {
	create_error('You have to pick a player.');
}
$db->query('SELECT 1
			FROM draft_leaders
			WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
			AND account_id = ' . $db->escapeNumber($var['PickedAccountID']));
if($db->nextRecord()) {
	create_error('You cannot pick another leader.');
}

require_once('alliance_pick.inc');
if($player->getAlliance()->getNumMembers() > min_alliance_members($player->getGameID())) {
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

forward(create_container('skeleton.php', 'alliance_pick.php'));

?>
