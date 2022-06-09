<?php declare(strict_types=1);
$leader_id = Smr\Request::getInt('leader_id');

$session = Smr\Session::getInstance();
$player = $session->getPlayer();
$alliance = $player->getAlliance();

$alliance->setLeaderID($leader_id);
$alliance->update();

$db = Smr\Database::getInstance();
$db->write('UPDATE player_has_alliance_role SET role_id = ' . $db->escapeNumber(ALLIANCE_ROLE_NEW_MEMBER) . ' WHERE ' . $player->getSQL() . ' AND alliance_id=' . $db->escapeNumber($player->getAllianceID()));
$db->write('UPDATE player_has_alliance_role SET role_id = ' . $db->escapeNumber(ALLIANCE_ROLE_LEADER) . ' WHERE account_id = ' . $db->escapeNumber($leader_id) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND alliance_id=' . $db->escapeNumber($player->getAllianceID()));

// Notify the new leader
$playerMessage = 'You are now the leader of ' . $alliance->getAllianceBBLink() . '!';
$player->sendMessageFromAllianceCommand($leader_id, $playerMessage);

Page::create('alliance_roster.php')->go();
