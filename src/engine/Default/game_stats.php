<?php declare(strict_types=1);

use Smr\Exceptions\PlayerNotFound;

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();

//get game id
$gameID = $var['game_id'];

$statsGame = SmrGame::getGame($gameID);
$template->assign('StatsGame', $statsGame);

$template->assign('PageTopic', 'Game Stats: ' . $statsGame->getName() . ' (' . $gameID . ')');

$db = Smr\Database::getInstance();
$dbResult = $db->read('SELECT count(*) total_players, IFNULL(MAX(experience),0) max_exp, IFNULL(MAX(alignment),0) max_align, IFNULL(MIN(alignment),0) min_alignment, IFNULL(MAX(kills),0) max_kills FROM player WHERE game_id = ' . $gameID . ' ORDER BY experience DESC');
if ($dbResult->hasRecord()) {
	$dbRecord = $dbResult->record();
	$template->assign('TotalPlayers', $dbRecord->getInt('total_players'));
	$template->assign('HighestExp', $dbRecord->getInt('max_exp'));
	$template->assign('HighestAlign', $dbRecord->getInt('max_align'));
	$template->assign('LowestAlign', $dbRecord->getInt('min_alignment'));
	$template->assign('HighestKills', $dbRecord->getInt('max_kills'));
}

$dbResult = $db->read('SELECT count(*) num_alliance FROM alliance WHERE game_id = ' . $gameID);
$template->assign('TotalAlliances', $dbResult->record()->getInt('num_alliance'));

// Get current account's player for this game (if any)
try {
	$player = SmrPlayer::getPlayer($session->getAccountID(), $gameID);
} catch (PlayerNotFound) {
	$player = null;
}

$playerExpRecords = Rankings::playerStats('experience', $gameID, 10);
$playerExpRanks = Rankings::collectRankings($playerExpRecords, $player);
$template->assign('ExperienceRankings', $playerExpRanks);

$playerKillRecords = Rankings::playerStats('kills', $gameID, 10);
$playerKillRanks = Rankings::collectRankings($playerKillRecords, $player);
$template->assign('KillRankings', $playerKillRanks);

$allianceTopTen = function(string $stat) use ($statsGame, $gameID, $player): array {
	$allianceRecords = Rankings::allianceStats($stat, $gameID, 10);
	$allianceRanks = Rankings::collectAllianceRankings($allianceRecords, $player);
	foreach ($allianceRanks as $rank => $info) {
		$alliance = $info['Alliance'];
		if ($statsGame->hasEnded()) {
			// If game has ended, offer a link to alliance roster details
			$data = ['game_id' => $gameID, 'alliance_id' => $alliance->getAllianceID()];
			$href = Page::create('previous_game_alliance_detail.php', $data)->href();
			$allianceName = create_link($href, $alliance->getAllianceDisplayName());
		} else {
			$allianceName = $alliance->getAllianceDisplayName();
		}
		$allianceRanks[$rank]['AllianceName'] = $allianceName;
	}
	return $allianceRanks;
};
$template->assign('AllianceExpRankings', $allianceTopTen('experience'));
$template->assign('AllianceKillRankings', $allianceTopTen('kills'));
