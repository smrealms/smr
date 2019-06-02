<?php
$template->assign('PageTopic', 'Alliance Kill Rankings');
Menu::rankings(1, 2);

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
					alliance_kills > '.$db->escapeNumber($player->getAlliance()->getKills()) . '
					OR (
						alliance_kills = '.$db->escapeNumber($player->getAlliance()->getKills()) . '
						AND alliance_name <= ' . $db->escapeString($player->getAllianceName()) . '
					)
				)');
	$db->nextRecord();
	$ourRank = $db->getInt('count(*)');
	$template->assign('OurRank', $ourRank);
}

$db->query('SELECT alliance_id, alliance_kills amount FROM alliance
			WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' ORDER BY amount DESC, alliance_name LIMIT 10');
$template->assign('Rankings', Rankings::collectAllianceRankings($db, $player, 0));

Rankings::calculateMinMaxRanks($ourRank, $numAlliances);

$lowerLimit = $var['MinRank'] - 1;
$db->query('SELECT alliance_id, alliance_kills amount FROM alliance
			WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' ORDER BY amount DESC, alliance_name LIMIT ' . $lowerLimit . ', ' . ($var['MaxRank'] - $lowerLimit));
$template->assign('FilteredRankings', Rankings::collectAllianceRankings($db, $player, $lowerLimit));

$template->assign('FilterRankingsHREF', SmrSession::getNewHREF(create_container('skeleton.php', 'rankings_alliance_kills.php')));
