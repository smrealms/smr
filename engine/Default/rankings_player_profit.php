<?php

$template->assign('PageTopic','Profit Rankings');

require_once(get_file_loc('Rankings.inc'));
require_once(get_file_loc('menu.inc'));
create_ranking_menu(0, 0);

$profitTypeEscaped = $db->escapeArray(array('Trade','Money','Profit'),false,true,':',false);

// what rank are we?
$db->query('SELECT count(*) FROM player JOIN player_hof USING(account_id, game_id)
			WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
			AND type = ' . $profitTypeEscaped . '
			AND (
				amount > '.$db->escapeNumber($player->getExperience()).'
				OR (
					amount = '.$db->escapeNumber($player->getExperience()).'
					AND player_name <= ' . $db->escapeString($player->getPlayerName()) . '
				)
			)');
$db->nextRecord();
$ourRank = $db->getInt('count(*)');
$template->assign('OurRank', $ourRank);

$totalPlayers = $player->getGame()->getTotalPlayers();
$template->assign('TotalPlayers', $totalPlayers);

$db->query('SELECT account_id, amount FROM player JOIN player_hof USING(account_id, game_id) WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND type = ' . $profitTypeEscaped . ' ORDER BY amount DESC, player_name LIMIT 10');
$template->assignByRef('Rankings', Rankings::collectRankings($db, $player, 0));

Rankings::calculateMinMaxRanks($ourRank, $totalPlayers);

$template->assign('FilterRankingsHREF', SmrSession::getNewHREF(create_container('skeleton.php', 'rankings_player_profit.php')));

$lowerLimit = $var['MinRank'] - 1;
$db->query('SELECT account_id, amount FROM player JOIN player_hof USING(account_id, game_id) WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND type = ' . $profitTypeEscaped . ' ORDER BY amount DESC, player_name LIMIT ' . $lowerLimit . ', ' . ($var['MaxRank'] - $lowerLimit));
$template->assignByRef('FilteredRankings', Rankings::collectRankings($db, $player, $lowerLimit));
?>