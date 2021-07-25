<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$player = $session->getPlayer();

$template->assign('PageTopic', 'Sector Death Rankings');

Menu::rankings(3, 0);

$db = Smr\Database::getInstance();
$dbResult = $db->read('SELECT * FROM sector WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' ORDER BY battles DESC, sector_id LIMIT 10');

$topTen = [];
foreach ($dbResult->records() as $index => $dbRecord) {
	// get current player
	$sectorID = $dbRecord->getInt('sector_id');
	$topTen[$index + 1] = SmrSector::getSector($player->getGameID(), $sectorID, false, $dbRecord);
}
$template->assign('TopTen', $topTen);

$min_rank = Smr\Request::getInt('min_rank', 1);
$max_rank = Smr\Request::getInt('max_rank', 10);

if ($min_rank < 0) {
	$min_rank = 1;
	$max_rank = 10;
}

$dbResult = $db->read('SELECT max(sector_id) FROM sector WHERE game_id = ' . $db->escapeNumber($player->getGameID()));
$total_sector = $dbResult->record()->getInt('max(sector_id)'); // we expect there to be at least 1 sector...

// Calculate the rank of the sector the player is currently in
$dbResult = $db->read('SELECT ranking
			FROM (
				SELECT sector_id,
				ROW_NUMBER() OVER (ORDER BY battles DESC, sector_id ASC) AS ranking
				FROM sector
				WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
			) t
			WHERE sector_id = ' . $db->escapeNumber($player->getSectorID())
);
$ourRank = $dbResult->record()->getInt('ranking');

$container = Page::create('skeleton.php', 'rankings_sector_kill.php');
$template->assign('SubmitHREF', $container->href());

list($minRank, $maxRank) = Rankings::calculateMinMaxRanks($ourRank, $total_sector);

$lowerLimit = $minRank - 1;
$dbResult = $db->read('SELECT * FROM sector WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' ORDER BY battles DESC, sector_id LIMIT ' . $lowerLimit . ', ' . ($maxRank - $lowerLimit));

$topCustom = [];
foreach ($dbResult->records() as $index => $dbRecord) {
	// get current player
	$sectorID = $dbRecord->getInt('sector_id');
	$topCustom[$minRank + $index] = SmrSector::getSector($player->getGameID(), $sectorID, false, $dbRecord);
}
$template->assign('TopCustom', $topCustom);
