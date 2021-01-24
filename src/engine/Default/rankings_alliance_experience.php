<?php declare(strict_types=1);
$template->assign('PageTopic', 'Alliance Experience Rankings');
Menu::rankings(1, 0);

$db->query('SELECT count(*) FROM alliance
			WHERE game_id = ' . $db->escapeNumber($player->getGameID()));
$db->requireRecord();
$numAlliances = $db->getInt('count(*)');

$ourRank = 0;
if ($player->hasAlliance()) {
	$db->query('SELECT ranking
				FROM (
					SELECT alliance_id,
					ROW_NUMBER() OVER (ORDER BY COALESCE(SUM(experience), 0) DESC, alliance_name ASC) AS ranking
					FROM alliance
					LEFT JOIN player USING (game_id, alliance_id)
					WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
					GROUP BY alliance_id
				) t
				WHERE alliance_id = ' . $db->escapeNumber($player->getAllianceID())
	);
	$db->requireRecord();
	$ourRank = $db->getInt('ranking');
	$template->assign('OurRank', $ourRank);
}

$expRanks = function (int $minRank, int $maxRank) use ($player, $db) : array {
	$offset = $minRank - 1;
	$limit = $maxRank - $offset;
	$db->query('SELECT alliance_id, COALESCE(SUM(experience), 0) amount
		FROM alliance
		LEFT JOIN player USING (game_id, alliance_id)
		WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
		GROUP BY alliance_id
		ORDER BY amount DESC, alliance_name ASC
		LIMIT ' . $offset . ', ' . $limit);
	return Rankings::collectAllianceRankings($db, $player, $offset);
};

$template->assign('Rankings', $expRanks(1, 10));

list($minRank, $maxRank) = Rankings::calculateMinMaxRanks($ourRank, $numAlliances);

$template->assign('FilteredRankings', $expRanks($minRank, $maxRank));

$template->assign('FilterRankingsHREF', SmrSession::getNewHREF(create_container('skeleton.php', 'rankings_alliance_experience.php')));
