<?php declare(strict_types=1);
$template->assign('PageTopic', 'Alliance Experience Rankings');
Menu::rankings(1, 0);

$db->query('SELECT count(*) FROM alliance
			WHERE game_id = ' . $db->escapeNumber($player->getGameID()));
$db->requireRecord();
$numAlliances = $db->getInt('count(*)');

$ourRank = 0;
if ($player->hasAlliance()) {
	$db->query('SELECT count(*)
				FROM (
					SELECT alliance_id, alliance_name, SUM(experience) amount
					FROM alliance
					LEFT JOIN player USING (game_id, alliance_id)
					WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
					GROUP BY alliance_id, alliance_name
				) t, (
					SELECT SUM(experience) amount
					FROM alliance
					LEFT JOIN player USING (game_id, alliance_id)
					WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
					AND alliance_id = ' . $db->escapeNumber($player->getAllianceID()) . '
				) us
				WHERE (
					t.amount > us.amount
					OR (
						t.amount = us.amount
						AND alliance_name <= ' . $db->escapeString($player->getAlliance()->getAllianceName()) . '
					)
				)');
	$db->requireRecord();
	$ourRank = $db->getInt('count(*)');
	$template->assign('OurRank', $ourRank);
}

$expRanks = function (int $minRank, int $maxRank) use ($player, $db) : array {
	$offset = $minRank - 1;
	$limit = $maxRank - $offset;
	$db->query('SELECT alliance_id, SUM(experience) amount
		FROM alliance
		LEFT JOIN player USING (game_id, alliance_id)
		WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
		GROUP BY alliance_id, alliance_name
		ORDER BY amount DESC, alliance_name
		LIMIT ' . $offset . ', ' . $limit);
	return Rankings::collectAllianceRankings($db, $player, $offset);
};

$template->assign('Rankings', $expRanks(1, 10));

list($minRank, $maxRank) = Rankings::calculateMinMaxRanks($ourRank, $numAlliances);

$template->assign('FilteredRankings', $expRanks($minRank, $maxRank));

$template->assign('FilterRankingsHREF', SmrSession::getNewHREF(create_container('skeleton.php', 'rankings_alliance_experience.php')));
