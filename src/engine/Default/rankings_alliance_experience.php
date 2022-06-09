<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$player = $session->getPlayer();

$template->assign('PageTopic', 'Alliance Experience Rankings');
Menu::rankings(1, 0);

$db = Smr\Database::getInstance();
$rankedStats = [];
$dbResult = $db->read('SELECT alliance_id, COALESCE(SUM(experience), 0) amount
		FROM alliance
		LEFT JOIN player USING (game_id, alliance_id)
		WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
		GROUP BY alliance_id
		ORDER BY amount DESC, alliance_name ASC');
foreach ($dbResult->records() as $dbRecord) {
	$rankedStats[$dbRecord->getInt('alliance_id')] = $dbRecord;
}

$ourRank = 0;
if ($player->hasAlliance()) {
	$ourRank = Rankings::ourRank($rankedStats, $player->getAllianceID());
	$template->assign('OurRank', $ourRank);
}

$template->assign('Rankings', Rankings::collectAllianceRankings($rankedStats, $player));

$numAlliances = count($rankedStats);
[$minRank, $maxRank] = Rankings::calculateMinMaxRanks($ourRank, $numAlliances);

$template->assign('FilteredRankings', Rankings::collectAllianceRankings($rankedStats, $player, $minRank, $maxRank));

$template->assign('FilterRankingsHREF', Page::create('rankings_alliance_experience.php')->href());
