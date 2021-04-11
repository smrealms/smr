<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$player = $session->getPlayer();

$template->assign('PageTopic', 'Sector Death Rankings');

Menu::rankings(3, 0);

$db = Smr\Database::getInstance();
$db->query('SELECT * FROM sector WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' ORDER BY battles DESC, sector_id LIMIT 10');

$rank = 1;
$topTen = [];
while ($db->nextRecord()) {
	// get current player
	$sectorID = $db->getInt('sector_id');
	$topTen[$rank++] = SmrSector::getSector($player->getGameID(), $sectorID, false, $db);
}
$template->assign('TopTen', $topTen);

$min_rank = Request::getInt('min_rank', 1);
$max_rank = Request::getInt('max_rank', 10);

if ($min_rank < 0) {
	$min_rank = 1;
	$max_rank = 10;
}

$db->query('SELECT max(sector_id) FROM sector WHERE game_id = ' . $db->escapeNumber($player->getGameID()));
if ($db->nextRecord()) {
	$total_sector = $db->getInt('max(sector_id)');
}

// Calculate the rank of the sector the player is currently in
$db->query('SELECT ranking
			FROM (
				SELECT sector_id,
				ROW_NUMBER() OVER (ORDER BY battles DESC, sector_id ASC) AS ranking
				FROM sector
				WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
			) t
			WHERE sector_id = ' . $db->escapeNumber($player->getSectorID())
);
$db->requireRecord();
$ourRank = $db->getInt('ranking');

$container = Page::create('skeleton.php', 'rankings_sector_kill.php');
$template->assign('SubmitHREF', $container->href());

list($minRank, $maxRank) = Rankings::calculateMinMaxRanks($ourRank, $total_sector);

$lowerLimit = $minRank - 1;
$db->query('SELECT * FROM sector WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' ORDER BY battles DESC, sector_id LIMIT ' . $lowerLimit . ', ' . ($maxRank - $lowerLimit));

$rank = $minRank;
$topCustom = [];
while ($db->nextRecord()) {
	// get current player
	$sectorID = $db->getInt('sector_id');
	$topCustom[$rank++] = SmrSector::getSector($player->getGameID(), $sectorID, false, $db);
}
$template->assign('TopCustom', $topCustom);
