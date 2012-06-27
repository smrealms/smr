<?php
$template->assign('PageTopic','Alliance Death Rankings');
require_once(get_file_loc('Rankings.inc'));
require_once(get_file_loc('menu.inc'));
create_ranking_menu(1, 3);

$db->query('SELECT count(*) FROM alliance
			WHERE game_id = ' . $db->escapeNumber($player->getGameID()));
$db->nextRecord();
$numAlliances = $db->getInt('count(*)');

$ourRank = 0;
if ($player->hasAlliance()) {
	$db->query('SELECT count(*)
				FROM alliance
				WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
				AND (
					alliance_deaths > '.$db->escapeNumber($player->getAlliance()->getDeaths()).'
					OR (
						alliance_deaths = '.$db->escapeNumber($player->getAlliance()->getDeaths()).'
						AND alliance_name <= ' . $db->escapeString($player->getAllianceName()) . '
					)
				)');
	$db->nextRecord();
	$ourRank = $db->getInt('count(*)');
	$template->assign('OurRank', $ourRank);
}

$db->query('SELECT alliance_id, alliance_deaths amount FROM alliance
			WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' ORDER BY amount DESC, alliance_name LIMIT 10');
$template->assignByRef('Rankings', Rankings::collectAllianceRankings($db, $player, 0));

Rankings::calculateMinMaxRanks($ourRank, $numAlliances);

$lowerLimit = $var['MinRank'] - 1;
$db->query('SELECT alliance_id, alliance_deaths amount FROM alliance
			WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' ORDER BY amount DESC, alliance_name LIMIT ' . $lowerLimit . ', ' . ($var['MaxRank'] - $lowerLimit));
$template->assignByRef('FilteredRankings', Rankings::collectAllianceRankings($db, $player, 0));

$template->assign('FilterRankingsHREF', SmrSession::getNewHREF(create_container('skeleton.php', 'rankings_alliance_death.php')));
?>