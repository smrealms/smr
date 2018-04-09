<?php

$template->assign('PageTopic','Death Rankings');

require_once(get_file_loc('Rankings.inc'));
require_once(get_file_loc('menu.inc'));
create_ranking_menu(0, 3);

// what rank are we?
$db->query('SELECT count(*) FROM player
			WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
				AND (
					deaths > '.$db->escapeNumber($player->getDeaths()).'
					OR (
						deaths = '.$db->escapeNumber($player->getDeaths()).'
						AND player_name <= ' . $db->escapeString($player->getPlayerName(), true) . '
					)
				)');
$db->nextRecord();
$ourRank = $db->getInt('count(*)');
$template->assign('OurRank', $ourRank);

$totalPlayers = $player->getGame()->getTotalPlayers();

$db->query('SELECT account_id, deaths amount FROM player WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' ORDER BY deaths DESC, player_name LIMIT 10');
$template->assign('Rankings', Rankings::collectRankings($db, $player, 0));

Rankings::calculateMinMaxRanks($ourRank, $totalPlayers);

$template->assign('FilterRankingsHREF', SmrSession::getNewHREF(create_container('skeleton.php', 'rankings_player_death.php')));

$lowerLimit = $var['MinRank'] - 1;
$db->query('SELECT account_id, deaths amount FROM player WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' ORDER BY deaths DESC, player_name LIMIT ' . $lowerLimit . ', ' . ($var['MaxRank'] - $lowerLimit));
$template->assign('FilteredRankings', Rankings::collectRankings($db, $player, $lowerLimit));
