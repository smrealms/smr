<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$player = $session->getPlayer();

$template->assign('PageTopic', 'Alliance Profit Rankings');
Menu::rankings(1, 1);

$profitType = implode(':', ['Trade', 'Money', 'Profit']);

$db = Smr\Database::getInstance();
$rankedStats = [];
$dbResult = $db->read('SELECT alliance_id, COALESCE(SUM(amount), 0) amount
	FROM alliance
	LEFT JOIN player p USING (game_id, alliance_id)
	LEFT JOIN player_hof ph ON p.account_id = ph.account_id AND p.game_id = ph.game_id AND ph.type = ' . $db->escapeString($profitType) . '
	WHERE p.game_id = ' . $db->escapeNumber($player->getGameID()) . '
	GROUP BY alliance_id
	ORDER BY amount DESC, alliance_name');
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

$template->assign('FilterRankingsHREF', Page::create('skeleton.php', 'rankings_alliance_profit.php')->href());
