<?php
$action = $var['action'];
if ($action == 'YES') {
	$alliance =& $player->getAlliance();

	if ($player->isAllianceLeader() && $alliance->getNumMembers() > 1) {
		create_error('You are the leader! You must hand over leadership first!');
	}

	// will this alliance be empty if we leave? (means one member right now)
	// Don't delete the Newbie Help Alliance!
	if ($alliance->getNumMembers() == 1 && $alliance->getAllianceID() != NHA_ID) {
		// Retain the alliance, but delete some auxilliary info
		$db->query('DELETE FROM alliance_bank_transactions
		            WHERE alliance_id = ' . $db->escapeNumber($player->getAllianceID()) . '
		            AND game_id = ' . $db->escapeNumber($player->getGameID()));
		$db->query('DELETE FROM alliance_thread
		            WHERE alliance_id = ' . $db->escapeNumber($player->getAllianceID()) . '
		            AND game_id = ' . $db->escapeNumber($player->getGameID()));
		$db->query('DELETE FROM alliance_thread_topic
		            WHERE alliance_id = ' . $db->escapeNumber($player->getAllianceID()) . '
		            AND game_id = ' . $db->escapeNumber($player->getGameID()));
		$db->query('DELETE FROM alliance_has_roles
		            WHERE alliance_id = ' . $db->escapeNumber($player->getAllianceID()) . '
		            AND game_id = ' . $db->escapeNumber($player->getGameID()));
		$db->query('UPDATE alliance SET leader_id = 0, discord_channel = NULL
		            WHERE alliance_id = ' . $db->escapeNumber($player->getAllianceID()) . '
		            AND game_id = ' . $db->escapeNumber($player->getGameID()));
	}

	// now leave the alliance
	$player->leaveAlliance();

}

$container = create_container('skeleton.php', 'current_sector.php');
forward($container);

?>
