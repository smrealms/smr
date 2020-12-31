<?php declare(strict_types=1);
$leader_id = Request::getInt('leader_id');

$alliance = $player->getAlliance();
$alliance->setLeaderID($leader_id);
$alliance->update();

$db->query('UPDATE player_has_alliance_role SET role_id = ' . $db->escapeNumber(ALLIANCE_ROLE_NEW_MEMBER) . ' WHERE ' . $player->getSQL() . ' AND alliance_id=' . $db->escapeNumber($player->getAllianceID()));
$db->query('UPDATE player_has_alliance_role SET role_id = ' . $db->escapeNumber(ALLIANCE_ROLE_LEADER) . ' WHERE account_id = ' . $db->escapeNumber($leader_id) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND alliance_id=' . $db->escapeNumber($player->getAllianceID()));

// Notify the new leader
$playerMessage = 'You are now the leader of ' . $alliance->getAllianceBBLink() . '!';
$player->sendMessageFromAllianceCommand($leader_id, $playerMessage);

forward(create_container('skeleton.php', 'alliance_roster.php'));
