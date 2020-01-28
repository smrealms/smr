<?php declare(strict_types=1);
$template->assign('PageTopic', 'Alliance Death Rankings');
Menu::rankings(1, 3);

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
					alliance_deaths > '.$db->escapeNumber($player->getAlliance()->getDeaths()) . '
					OR (
						alliance_deaths = '.$db->escapeNumber($player->getAlliance()->getDeaths()) . '
						AND alliance_name <= ' . $db->escapeString($player->getAlliance()->getAllianceName()) . '
					)
				)');
	$db->requireRecord();
	$ourRank = $db->getInt('count(*)');
	$template->assign('OurRank', $ourRank);
}

$template->assign('Rankings', Rankings::allianceRanks('deaths'));

list($minRank, $maxRank) = Rankings::calculateMinMaxRanks($ourRank, $numAlliances);

$template->assign('FilteredRankings', Rankings::allianceRanks('deaths', $minRank, $maxRank));

$template->assign('FilterRankingsHREF', SmrSession::getNewHREF(create_container('skeleton.php', 'rankings_alliance_death.php')));
