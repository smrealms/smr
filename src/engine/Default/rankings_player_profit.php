<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$db = Smr\Database::getInstance();
$session = Smr\Session::getInstance();
$player = $session->getPlayer();

$template->assign('PageTopic', 'Profit Rankings');

Menu::rankings(0, 1);

$profitType = array('Trade', 'Money', 'Profit');
$profitTypeEscaped = $db->escapeArray($profitType, ':', false);

// what rank are we?
$dbResult = $db->read('SELECT ranking
			FROM (
				SELECT player_id,
				ROW_NUMBER() OVER (ORDER BY COALESCE(ph.amount, 0) DESC, player_name ASC) AS ranking
				FROM player p
				LEFT JOIN player_hof ph ON p.account_id = ph.account_id AND p.game_id = ph.game_id AND ph.type = ' . $profitTypeEscaped . '
				WHERE p.game_id = ' . $db->escapeNumber($player->getGameID()) . '
			) t
			WHERE player_id = ' . $db->escapeNumber($player->getPlayerID())
);
$ourRank = $dbResult->record()->getInt('ranking');
$template->assign('OurRank', $ourRank);

$totalPlayers = $player->getGame()->getTotalPlayers();

$profitRanks = function(int $minRank, int $maxRank) use ($player, $db, $profitTypeEscaped) : array {
	$offset = $minRank - 1;
	$limit = $maxRank - $offset;
	$dbResult = $db->read('SELECT p.*, COALESCE(ph.amount,0) amount FROM player p LEFT JOIN player_hof ph ON p.account_id = ph.account_id AND p.game_id = ph.game_id AND ph.type = ' . $profitTypeEscaped . ' WHERE p.game_id = ' . $db->escapeNumber($player->getGameID()) . ' ORDER BY amount DESC, player_name ASC LIMIT ' . $offset . ', ' . $limit);
	return Rankings::collectRankings($dbResult, $player, $minRank);
};

$template->assign('Rankings', $profitRanks(1, 10));

list($minRank, $maxRank) = Rankings::calculateMinMaxRanks($ourRank, $totalPlayers);

$template->assign('FilterRankingsHREF', Page::create('skeleton.php', 'rankings_player_profit.php')->href());

$template->assign('FilteredRankings', $profitRanks($minRank, $maxRank));
