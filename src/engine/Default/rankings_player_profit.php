<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$db = Smr\Database::getInstance();
$session = Smr\Session::getInstance();
$player = $session->getPlayer();

$template->assign('PageTopic', 'Profit Rankings');

Menu::rankings(0, 1);

$profitType = array('Trade', 'Money', 'Profit');

$rankedStats = [];
$dbResult = $db->read('SELECT p.*, COALESCE(ph.amount,0) amount FROM player p LEFT JOIN player_hof ph ON p.account_id = ph.account_id AND p.game_id = ph.game_id AND ph.type = ' . $db->escapeArray($profitType, ':', false) . ' WHERE p.game_id = ' . $db->escapeNumber($player->getGameID()) . ' ORDER BY amount DESC, player_name ASC');
foreach ($dbResult->records() as $dbRecord) {
	$rankedStats[$dbRecord->getInt('player_id')] = $dbRecord;
}

// what rank are we?
$ourRank = Rankings::ourRank($rankedStats, $player->getPlayerID());
$template->assign('OurRank', $ourRank);

$template->assign('Rankings', Rankings::collectRankings($rankedStats, $player));

$totalPlayers = count($rankedStats);
list($minRank, $maxRank) = Rankings::calculateMinMaxRanks($ourRank, $totalPlayers);

$template->assign('FilterRankingsHREF', Page::create('skeleton.php', 'rankings_player_profit.php')->href());

$template->assign('FilteredRankings', Rankings::collectRankings($rankedStats, $player, $minRank, $maxRank));
