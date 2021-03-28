<?php declare(strict_types=1);
$template->assign('PageTopic', 'Alliance Kill Rankings');
Menu::rankings(1, 2);

$db->query('SELECT count(*) FROM alliance
			WHERE game_id = ' . $db->escapeNumber($player->getGameID()));
$db->requireRecord();
$numAlliances = $db->getInt('count(*)');

$ourRank = 0;
if ($player->hasAlliance()) {
	$db->query('SELECT ranking
				FROM (
					SELECT alliance_id,
					ROW_NUMBER() OVER (ORDER BY alliance_kills DESC, alliance_name ASC) AS ranking
					FROM alliance
					WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
				) t
				WHERE alliance_id = ' . $db->escapeNumber($player->getAllianceID())
	);
	$db->requireRecord();
	$ourRank = $db->getInt('ranking');
	$template->assign('OurRank', $ourRank);
}

$template->assign('Rankings', Rankings::allianceRanks('kills'));

list($minRank, $maxRank) = Rankings::calculateMinMaxRanks($ourRank, $numAlliances);

$template->assign('FilteredRankings', Rankings::allianceRanks('kills', $minRank, $maxRank));

$template->assign('FilterRankingsHREF', Page::create('skeleton.php', 'rankings_alliance_kills.php')->href());
