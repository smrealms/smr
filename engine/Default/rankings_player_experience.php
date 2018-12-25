<?php

$template->assign('PageTopic','Experience Rankings');

require_once(get_file_loc('menu.inc'));
create_ranking_menu(0, 0);

// what rank are we?
$ourRank = $player->getExperienceRank();
$template->assign('OurRank', $ourRank);

$totalPlayers = $player->getGame()->getTotalPlayers();

$db->query('SELECT *, experience AS amount FROM player WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' ORDER BY experience DESC, player_name LIMIT 10');
$template->assign('Rankings', Rankings::collectRankings($db, $player, 0));

Rankings::calculateMinMaxRanks($ourRank, $totalPlayers);

$template->assign('FilterRankingsHREF', SmrSession::getNewHREF(create_container('skeleton.php', 'rankings_player_experience.php')));

$lowerLimit = $var['MinRank'] - 1;
$db->query('SELECT *, experience AS amount FROM player WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' ORDER BY experience DESC, player_name LIMIT ' . $lowerLimit . ', ' . ($var['MaxRank'] - $lowerLimit));
$template->assign('FilteredRankings', Rankings::collectRankings($db, $player, $lowerLimit));
