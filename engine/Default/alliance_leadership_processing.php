<?php declare(strict_types=1);
$leader_id = $_REQUEST['leader_id'];
if (!is_numeric($leader_id)) {
	create_error('Leader ID must be a number.');
}

$alliance = $player->getAlliance();
$alliance->setLeaderID($leader_id);
$alliance->update();

$db->query('UPDATE player_has_alliance_role SET role_id = ' . $db->escapeNumber(ALLIANCE_ROLE_NEW_MEMBER) . ' WHERE account_id = ' . $db->escapeNumber($player->getAccountID()) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND alliance_id=' . $db->escapeNumber($player->getAllianceID()));
$db->query('UPDATE player_has_alliance_role SET role_id = ' . $db->escapeNumber(ALLIANCE_ROLE_LEADER) . ' WHERE account_id = ' . $db->escapeNumber($leader_id) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND alliance_id=' . $db->escapeNumber($player->getAllianceID()));

// Notify the new leader
$playerMessage = 'You are now the leader of [alliance=' . $player->getAllianceID() . ']!';
$player->sendMessageFromAllianceCommand($leader_id, $playerMessage);

forward(create_container('skeleton.php', 'alliance_roster.php'));
