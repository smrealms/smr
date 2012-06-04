<?php

$template->assign('PageTopic','Experience Rankings');

require_once(get_file_loc('Rankings.inc'));
require_once(get_file_loc('menu.inc'));
create_ranking_menu(0, 1);

// what rank are we?
$db->query('SELECT count(*) FROM player
			WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
			AND (
				experience > '.$db->escapeNumber($player->getExperience()).'
				OR (
					experience = '.$db->escapeNumber($player->getExperience()).'
					AND player_name <= ' . $db->escapeString($player->getPlayerName()) . '
				)
			)');
$db->nextRecord();
$ourRank = $db->getInt('count(*)');
$template->assign('OurRank', $ourRank);

$totalPlayers = $player->getGame()->getTotalPlayers();

$db->query('SELECT account_id, experience amount FROM player WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' ORDER BY experience DESC, player_name LIMIT 10');
$template->assignByRef('Rankings', Rankings::collectRankings($db, $player, 0));

Rankings::calculateMinMaxRanks($ourRank, $totalPlayers);

$template->assign('FilterRankingsHREF', SmrSession::getNewHREF(create_container('skeleton.php', 'rankings_player_experience.php')));

$lowerLimit = $var['MinRank'] - 1;
$db->query('SELECT account_id, experience amount FROM player WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' ORDER BY experience DESC, player_name LIMIT ' . $lowerLimit . ', ' . ($var['MaxRank'] - $lowerLimit));
$template->assignByRef('FilteredRankings', Rankings::collectRankings($db, $player, $lowerLimit));
?>