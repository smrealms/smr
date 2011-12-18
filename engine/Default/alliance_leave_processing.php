<?php
$action = $var['action'];
if ($action == 'YES') {
	$alliance =& $player->getAlliance();
	// will this alliance be empty if we leave? (means one member right now)
	if ($player->isAllianceLeader() && $alliance->getNumMembers() > 1) {
		create_error('You are the leader! You must hand over leadership first!');
	}
	$player->leaveAlliance();
	//$db->query('DELETE FROM alliance WHERE alliance_id = '.$player->getAllianceID().' AND ' .
										  //'game_id = '.$player->getGameID());
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
	$db->query('UPDATE alliance SET leader_id = 0
				WHERE alliance_id = ' . $db->escapeNumber($player->getAllianceID()) . '
				AND game_id = ' . $db->escapeNumber($player->getGameID()));

}

$container = create_container('skeleton.php');
if ($player->isLandedOnPlanet()) {
	$container['body'] = 'planet_main.php';
}
else {
	$container['body'] = 'current_sector.php';
}

forward($container);

?>