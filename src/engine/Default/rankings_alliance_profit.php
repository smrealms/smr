<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$player = $session->getPlayer();

$template->assign('PageTopic', 'Alliance Profit Rankings');
Menu::rankings(1, 1);

$db = Smr\Database::getInstance();
$dbResult = $db->read('SELECT count(*) FROM alliance
			WHERE game_id = ' . $db->escapeNumber($player->getGameID()));
$numAlliances = $dbResult->record()->getInt('count(*)');
$profitType = array('Trade', 'Money', 'Profit');
$profitTypeEscaped = $db->escapeArray($profitType, ':', false);

$ourRank = 0;
if ($player->hasAlliance()) {
	$dbResult = $db->read('SELECT ranking
				FROM (
					SELECT alliance_id,
					ROW_NUMBER() OVER (ORDER BY COALESCE(SUM(amount), 0) DESC, alliance_name ASC) AS ranking
					FROM alliance
					LEFT JOIN player p USING (game_id, alliance_id)
					LEFT JOIN player_hof ph ON p.account_id = ph.account_id AND p.game_id = ph.game_id AND ph.type = ' . $profitTypeEscaped . '
					WHERE p.game_id = ' . $db->escapeNumber($player->getGameID()) . '
					GROUP BY alliance_id
				) AS t
				WHERE alliance_id = ' . $db->escapeNumber($player->getAllianceID())
	);
	$ourRank = $dbResult->record()->getInt('ranking');
	$template->assign('OurRank', $ourRank);
}

$profitRanks = function(int $minRank, int $maxRank) use ($player, $db, $profitTypeEscaped) : array {
	$offset = $minRank - 1;
	$limit = $maxRank - $offset;
	$dbResult = $db->read('SELECT alliance_id, COALESCE(SUM(amount), 0) amount
			FROM alliance
			LEFT JOIN player p USING (game_id, alliance_id)
			LEFT JOIN player_hof ph ON p.account_id = ph.account_id AND p.game_id = ph.game_id AND ph.type = ' . $profitTypeEscaped . '
			WHERE p.game_id = ' . $db->escapeNumber($player->getGameID()) . '
			GROUP BY alliance_id
			ORDER BY amount DESC, alliance_name
			LIMIT ' . $offset . ', ' . $limit);
	return Rankings::collectAllianceRankings($dbResult, $player, $minRank);
};

$template->assign('Rankings', $profitRanks(1, 10));

list($minRank, $maxRank) = Rankings::calculateMinMaxRanks($ourRank, $numAlliances);

$template->assign('FilteredRankings', $profitRanks($minRank, $maxRank));

$template->assign('FilterRankingsHREF', Page::create('skeleton.php', 'rankings_alliance_profit.php')->href());
