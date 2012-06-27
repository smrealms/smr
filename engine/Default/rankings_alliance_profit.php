<?php
$template->assign('PageTopic','Alliance Experience Rankings');
require_once(get_file_loc('Rankings.inc'));
require_once(get_file_loc('menu.inc'));
create_ranking_menu(1, 0);

$db->query('SELECT count(*) FROM alliance
			WHERE game_id = ' . $db->escapeNumber($player->getGameID()));
$db->nextRecord();
$numAlliances = $db->getInt('count(*)');

$ourRank = 0;
if ($player->hasAlliance()) {
	$db->query('SELECT count(*)
				FROM (
					SELECT alliance_id, alliance_name, SUM(experience) experience
					FROM alliance
					LEFT JOIN player USING (game_id, alliance_id)
					WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
					GROUP BY alliance_id, alliance_name
				) t, (
					SELECT SUM(experience) experience
					FROM alliance
					LEFT JOIN player USING (game_id, alliance_id)
					WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
					AND alliance_id = ' . $db->escapeNumber($player->getAllianceID()) . '
				) us
				WHERE (
					t.experience > us.experience
					OR (
						t.experience = us.experience
						AND alliance_name <= ' . $db->escapeString($player->getAllianceName()) . '
					)
				)');
	$db->nextRecord();
	$ourRank = $db->getInt('count(*)');
	$template->assign('OurRank', $ourRank);
}

$db->query('SELECT alliance_id, SUM(experience) amount
			FROM alliance
			LEFT JOIN player USING (game_id, alliance_id)
			WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
			GROUP BY alliance_id, alliance_name
			ORDER BY amount DESC, alliance_name
			LIMIT 10');
$template->assignByRef('Rankings', Rankings::collectAllianceRankings($db, $player, 0));

Rankings::calculateMinMaxRanks($ourRank, $numAlliances);

$lowerLimit = $var['MinRank'] - 1;
$db->query('SELECT alliance_id, SUM(experience) amount
			FROM alliance
			LEFT JOIN player USING (game_id, alliance_id)
			WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
			GROUP BY alliance_id, alliance_name
			ORDER BY amount DESC, alliance_name
			LIMIT ' . $lowerLimit . ', ' . ($var['MaxRank'] - $lowerLimit));
$template->assignByRef('FilteredRankings', Rankings::collectAllianceRankings($db, $player, 0));

$template->assign('FilterRankingsHREF', SmrSession::getNewHREF(create_container('skeleton.php', 'rankings_alliance_experience.php')));
?>