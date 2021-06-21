<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$player = $session->getPlayer();

$template->assign('PageTopic', 'Alliance Kill Rankings');
Menu::rankings(1, 2);

$db = Smr\Database::getInstance();
$dbResult = $db->read('SELECT count(*) FROM alliance
			WHERE game_id = ' . $db->escapeNumber($player->getGameID()));
$numAlliances = $dbResult->record()->getInt('count(*)');

$ourRank = 0;
if ($player->hasAlliance()) {
	$dbResult = $db->read('SELECT ranking
				FROM (
					SELECT alliance_id,
					ROW_NUMBER() OVER (ORDER BY alliance_kills DESC, alliance_name ASC) AS ranking
					FROM alliance
					WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
				) t
				WHERE alliance_id = ' . $db->escapeNumber($player->getAllianceID())
	);
	$ourRank = $dbResult->record()->getInt('ranking');
	$template->assign('OurRank', $ourRank);
}

$template->assign('Rankings', Rankings::allianceRanks('kills'));

list($minRank, $maxRank) = Rankings::calculateMinMaxRanks($ourRank, $numAlliances);

$template->assign('FilteredRankings', Rankings::allianceRanks('kills', $minRank, $maxRank));

$template->assign('FilterRankingsHREF', Page::create('skeleton.php', 'rankings_alliance_kills.php')->href());
