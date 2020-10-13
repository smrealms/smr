<?php declare(strict_types=1);
$leaderPlayerID = Request::getInt('leader_player_id');

$alliance = $player->getAlliance();
$alliance->setLeaderPlayerID($leaderPlayerID);
$alliance->update();

$db->query('UPDATE player_has_alliance_role SET role_id = ' . $db->escapeNumber(ALLIANCE_ROLE_NEW_MEMBER) . ' WHERE ' . $player->getSQL() . ' AND alliance_id=' . $db->escapeNumber($player->getAllianceID()));
$db->query('UPDATE player_has_alliance_role SET role_id = ' . $db->escapeNumber(ALLIANCE_ROLE_LEADER) . ' WHERE player_id = ' . $db->escapeNumber($leaderPlayerID) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND alliance_id=' . $db->escapeNumber($player->getAllianceID()));

// Notify the new leader
$playerMessage = 'You are now the leader of ' . $alliance->getAllianceBBLink() . '!';
$player->sendMessageFromAllianceCommand($leaderPlayerID, $playerMessage);

forward(create_container('skeleton.php', 'alliance_roster.php'));
