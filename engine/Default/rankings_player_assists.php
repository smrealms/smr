<?php

$template->assign('PageTopic', 'Assist Rankings');

Menu::rankings(0, 4);

// what rank are we?
$ourRank = $player->getAssistsRank();
$template->assign('OurRank', $ourRank);

$totalPlayers = $player->getGame()->getTotalPlayers();

$db->query('SELECT *, assists AS amount FROM player WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' ORDER BY assists DESC, player_name LIMIT 10');
$template->assign('Rankings', Rankings::collectRankings($db, $player, 0));

Rankings::calculateMinMaxRanks($ourRank, $totalPlayers);

$template->assign('FilterRankingsHREF', SmrSession::getNewHREF(create_container('skeleton.php', 'rankings_player_assists.php')));

$lowerLimit = $var['MinRank'] - 1;
$db->query('SELECT *, assists AS amount FROM player WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' ORDER BY assists DESC, player_name LIMIT ' . $lowerLimit . ', ' . ($var['MaxRank'] - $lowerLimit));
$template->assign('FilteredRankings', Rankings::collectRankings($db, $player, $lowerLimit));
