<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$var = Smr\Session::getInstance()->getCurrentVar();

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

$dbResult = $db->read('SELECT * FROM player WHERE game_id = ' . $gameID . ' ORDER BY experience DESC LIMIT 10');
if ($dbResult->hasRecord()) {
	$expRankings = [];
	foreach ($dbResult->records() as $index => $dbRecord) {
		$expRankings[$index + 1] = SmrPlayer::getPlayer($dbRecord->getInt('account_id'), $gameID, false, $dbRecord);
	}
	$template->assign('ExperienceRankings', $expRankings);
}


$dbResult = $db->read('SELECT * FROM player WHERE game_id = ' . $gameID . ' ORDER BY kills DESC LIMIT 10');
if ($dbResult->hasRecord()) {
	$killRankings = [];
	foreach ($dbResult->records() as $index => $dbRecord) {
		$killRankings[$index + 1] = SmrPlayer::getPlayer($dbRecord->getInt('account_id'), $gameID, false, $dbRecord);
	}
	$template->assign('KillRankings', $killRankings);
}

function allianceTopTen(SmrGame $game, string $field): array {
	$gameID = $game->getGameID();
	$db = Smr\Database::getInstance();
	$dbResult = $db->read('SELECT alliance_id, SUM(' . $field . ') amount
				FROM alliance
				LEFT JOIN player USING (game_id, alliance_id)
				WHERE game_id = ' . $db->escapeNumber($gameID) . '
				GROUP BY alliance_id, alliance_name
				ORDER BY amount DESC, alliance_name
				LIMIT 10');
	$rankings = [];
	foreach ($dbResult->records() as $index => $dbRecord) {
		$alliance = SmrAlliance::getAlliance($dbRecord->getInt('alliance_id'), $gameID);
		$rankings[$index + 1]['Amount'] = $dbRecord->getInt('amount');
		if ($game->hasEnded()) {
			// If game has ended, offer a link to alliance roster details
			$data = ['game_id' => $gameID, 'alliance_id' => $alliance->getAllianceID()];
			$href = Page::create('skeleton.php', 'previous_game_alliance_detail.php', $data)->href();
			$rankings[$index + 1]['AllianceName'] = create_link($href, $alliance->getAllianceDisplayName());
		} else {
			$rankings[$index + 1]['AllianceName'] = $alliance->getAllianceDisplayName();
		}
	}
	return $rankings;
}

$template->assign('AllianceExpRankings', allianceTopTen($statsGame, 'experience'));
$template->assign('AllianceKillRankings', allianceTopTen($statsGame, 'kills'));
