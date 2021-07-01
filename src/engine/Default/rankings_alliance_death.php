<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$player = $session->getPlayer();

$template->assign('PageTopic', 'Alliance Death Rankings');
Menu::rankings(1, 3);

$db = Smr\Database::getInstance();
$dbResult = $db->read('SELECT count(*) FROM alliance
			WHERE game_id = ' . $db->escapeNumber($player->getGameID()));
$numAlliances = $dbResult->record()->getInt('count(*)');

$ourRank = 0;
if ($player->hasAlliance()) {
	$dbResult = $db->read('SELECT ranking
				FROM (
					SELECT alliance_id,
					ROW_NUMBER() OVER (ORDER BY alliance_deaths DESC, alliance_name ASC) AS ranking
					FROM alliance
					WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
				) t
				WHERE alliance_id = ' . $db->escapeNumber($player->getAllianceID())
	);
	$ourRank = $dbResult->record()->getInt('ranking');
	$template->assign('OurRank', $ourRank);
}

$template->assign('Rankings', Rankings::allianceRanks('deaths'));

list($minRank, $maxRank) = Rankings::calculateMinMaxRanks($ourRank, $numAlliances);

$template->assign('FilteredRankings', Rankings::allianceRanks('deaths', $minRank, $maxRank));

$template->assign('FilterRankingsHREF', Page::create('skeleton.php', 'rankings_alliance_death.php')->href());
