<?php declare(strict_types=1);
$template->assign('PageTopic', 'Alliance Kill Rankings');
Menu::rankings(1, 2);

$db->query('SELECT count(*) FROM alliance
			WHERE game_id = ' . $db->escapeNumber($player->getGameID()));
$db->requireRecord();
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
						AND alliance_name <= ' . $db->escapeString($player->getAlliance()->getAllianceName()) . '
					)
				)');
	$db->requireRecord();
	$ourRank = $db->getInt('count(*)');
	$template->assign('OurRank', $ourRank);
}

$template->assign('Rankings', Rankings::allianceRanks('kills'));

list($minRank, $maxRank) = Rankings::calculateMinMaxRanks($ourRank, $numAlliances);

$template->assign('FilteredRankings', Rankings::allianceRanks('kills', $minRank, $maxRank));

$template->assign('FilterRankingsHREF', SmrSession::getNewHREF(create_container('skeleton.php', 'rankings_alliance_kills.php')));
