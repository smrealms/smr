<?php

$template->assign('PageTopic','Profit Rankings');

require_once(get_file_loc('Rankings.inc'));
require_once(get_file_loc('menu.inc'));
create_ranking_menu(0, 0);

$profitType = array('Trade','Money','Profit');
$profitTypeEscaped = $db->escapeArray($profitType,false,true,':',false);

// what rank are we?
$db->query('SELECT count(*)
			FROM player p
			LEFT JOIN player_hof ph ON p.account_id = ph.account_id AND p.game_id = ph.game_id AND ph.type = ' . $profitTypeEscaped . '
			WHERE p.game_id = ' . $db->escapeNumber($player->getGameID()) . '
			AND (
				amount > '.$db->escapeNumber($player->getHOF($profitType)).'
				OR (
					COALESCE(amount,0) = '.$db->escapeNumber($player->getHOF($profitType)).'
					AND player_name <= ' . $db->escapeString($player->getPlayerName()) . '
				)
			)');
$db->nextRecord();
$ourRank = $db->getInt('count(*)');
$template->assign('OurRank', $ourRank);

$totalPlayers = $player->getGame()->getTotalPlayers();
$template->assign('TotalPlayers', $totalPlayers);

$db->query('SELECT p.account_id, COALESCE(ph.amount,0) amount FROM player p LEFT JOIN player_hof ph ON p.account_id = ph.account_id AND p.game_id = ph.game_id AND ph.type = ' . $profitTypeEscaped . ' WHERE p.game_id = ' . $db->escapeNumber($player->getGameID()) . ' ORDER BY amount DESC, player_name ASC LIMIT 10');
$template->assignByRef('Rankings', Rankings::collectRankings($db, $player, 0));

Rankings::calculateMinMaxRanks($ourRank, $totalPlayers);

$template->assign('FilterRankingsHREF', SmrSession::getNewHREF(create_container('skeleton.php', 'rankings_player_profit.php')));

$lowerLimit = $var['MinRank'] - 1;
$db->query('SELECT p.account_id, COALESCE(ph.amount,0) amount FROM player p LEFT JOIN player_hof ph ON p.account_id = ph.account_id AND p.game_id = ph.game_id AND ph.type = ' . $profitTypeEscaped . ' WHERE p.game_id = ' . $db->escapeNumber($player->getGameID()) . ' ORDER BY amount DESC, player_name ASC LIMIT ' . $lowerLimit . ', ' . ($var['MaxRank'] - $lowerLimit));
$template->assignByRef('FilteredRankings', Rankings::collectRankings($db, $player, $lowerLimit));
?>