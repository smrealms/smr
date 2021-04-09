<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$player = $session->getPlayer();

$template->assign('PageTopic', 'Alliance Death Rankings');
Menu::rankings(1, 3);

$db->query('SELECT count(*) FROM alliance
			WHERE game_id = ' . $db->escapeNumber($player->getGameID()));
$db->requireRecord();
$numAlliances = $db->getInt('count(*)');

$ourRank = 0;
if ($player->hasAlliance()) {
	$db->query('SELECT ranking
				FROM (
					SELECT alliance_id,
					ROW_NUMBER() OVER (ORDER BY alliance_deaths DESC, alliance_name ASC) AS ranking
					FROM alliance
					WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
				) t
				WHERE alliance_id = ' . $db->escapeNumber($player->getAllianceID())
	);
	$db->requireRecord();
	$ourRank = $db->getInt('ranking');
	$template->assign('OurRank', $ourRank);
}

$template->assign('Rankings', Rankings::allianceRanks('deaths'));

list($minRank, $maxRank) = Rankings::calculateMinMaxRanks($ourRank, $numAlliances);

$template->assign('FilteredRankings', Rankings::allianceRanks('deaths', $minRank, $maxRank));

$template->assign('FilterRankingsHREF', Page::create('skeleton.php', 'rankings_alliance_death.php')->href());
