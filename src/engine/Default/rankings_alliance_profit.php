<?php declare(strict_types=1);
$template->assign('PageTopic', 'Alliance Profit Rankings');
Menu::rankings(1, 1);

$db->query('SELECT count(*) FROM alliance
			WHERE game_id = ' . $db->escapeNumber($player->getGameID()));
$db->requireRecord();
$numAlliances = $db->getInt('count(*)');
$profitType = array('Trade', 'Money', 'Profit');
$profitTypeEscaped = $db->escapeArray($profitType, ':', false);

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
						AND alliance_name <= ' . $db->escapeString($player->getAlliance()->getAllianceName()) . '
					)
				)');
	$db->requireRecord();
	$ourRank = $db->getInt('count(*)');
	$template->assign('OurRank', $ourRank);
}

$profitRanks = function (int $minRank, int $maxRank) use ($player, $db, $profitTypeEscaped) : array {
	$offset = $minRank - 1;
	$limit = $maxRank - $offset;
	$db->query('SELECT alliance_id, SUM(amount) amount
			FROM alliance
			LEFT JOIN player p USING (game_id, alliance_id)
			LEFT JOIN player_hof ph ON p.account_id = ph.account_id AND p.game_id = ph.game_id AND ph.type = ' . $profitTypeEscaped . '
			WHERE p.game_id = ' . $db->escapeNumber($player->getGameID()) . '
			GROUP BY alliance_id, alliance_name
			ORDER BY amount DESC, alliance_name
			LIMIT ' . $offset . ', ' . $limit);
	return Rankings::collectAllianceRankings($db, $player, $offset);
};

$template->assign('Rankings', $profitRanks(1, 10));

list($minRank, $maxRank) = Rankings::calculateMinMaxRanks($ourRank, $numAlliances);

$template->assign('FilteredRankings', $profitRanks($minRank, $maxRank));

$template->assign('FilterRankingsHREF', SmrSession::getNewHREF(create_container('skeleton.php', 'rankings_alliance_profit.php')));
