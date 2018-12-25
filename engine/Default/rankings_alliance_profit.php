<?php
$template->assign('PageTopic','Alliance Profit Rankings');
require_once(get_file_loc('menu.inc'));
create_ranking_menu(1, 1);

$db->query('SELECT count(*) FROM alliance
			WHERE game_id = ' . $db->escapeNumber($player->getGameID()));
$db->nextRecord();
$numAlliances = $db->getInt('count(*)');
$profitType = array('Trade','Money','Profit');
$profitTypeEscaped = $db->escapeArray($profitType,false,true,':',false);

$ourRank = 0;
if ($player->hasAlliance()) {
	$db->query('SELECT count(*)
				FROM (
					SELECT alliance_id, alliance_name, SUM(amount) amount
					FROM alliance
					LEFT JOIN player p USING (game_id, alliance_id)
					LEFT JOIN player_hof ph ON p.account_id = ph.account_id AND p.game_id = ph.game_id AND ph.type = ' . $profitTypeEscaped . '
					WHERE p.game_id = ' . $db->escapeNumber($player->getGameID()) . '
					GROUP BY alliance_id, alliance_name
				) t, (
					SELECT SUM(amount) amount
					FROM alliance
					LEFT JOIN player p USING (game_id, alliance_id)
					LEFT JOIN player_hof ph ON p.account_id = ph.account_id AND p.game_id = ph.game_id AND ph.type = ' . $profitTypeEscaped . '
					WHERE p.game_id = ' . $db->escapeNumber($player->getGameID()) . '
					AND alliance_id = ' . $db->escapeNumber($player->getAllianceID()) . '
				) us
				WHERE (
					t.amount > us.amount
					OR (
						COALESCE(t.amount,0) = COALESCE(us.amount,0)
						AND alliance_name <= ' . $db->escapeString($player->getAllianceName()) . '
					)
				)');
	$db->nextRecord();
	$ourRank = $db->getInt('count(*)');
	$template->assign('OurRank', $ourRank);
}

$db->query('SELECT alliance_id, SUM(amount) amount
			FROM alliance
			LEFT JOIN player p USING (game_id, alliance_id)
			LEFT JOIN player_hof ph ON p.account_id = ph.account_id AND p.game_id = ph.game_id AND ph.type = ' . $profitTypeEscaped . '
			WHERE p.game_id = ' . $db->escapeNumber($player->getGameID()) . '
			GROUP BY alliance_id, alliance_name
			ORDER BY amount DESC, alliance_name
			LIMIT 10');
$template->assign('Rankings', Rankings::collectAllianceRankings($db, $player, 0));

Rankings::calculateMinMaxRanks($ourRank, $numAlliances);

$lowerLimit = $var['MinRank'] - 1;
$db->query('SELECT alliance_id, SUM(amount) amount
			FROM alliance
			LEFT JOIN player p USING (game_id, alliance_id)
			LEFT JOIN player_hof ph ON p.account_id = ph.account_id AND p.game_id = ph.game_id AND ph.type = ' . $profitTypeEscaped . '
			WHERE p.game_id = ' . $db->escapeNumber($player->getGameID()) . '
			GROUP BY alliance_id, alliance_name
			ORDER BY amount DESC, alliance_name
			LIMIT ' . $lowerLimit . ', ' . ($var['MaxRank'] - $lowerLimit));
$template->assign('FilteredRankings', Rankings::collectAllianceRankings($db, $player, $lowerLimit));

$template->assign('FilterRankingsHREF', SmrSession::getNewHREF(create_container('skeleton.php', 'rankings_alliance_experience.php')));
