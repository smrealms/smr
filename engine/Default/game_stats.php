<?php declare(strict_types=1);

//get game id
$gameID = $var['game_id'];

$statsGame = SmrGame::getGame($gameID);
$template->assign('StatsGame', $statsGame);

$template->assign('PageTopic', 'Game Stats: ' . $statsGame->getName() . ' (' . $gameID . ')');

$db->query('SELECT count(*) total_players, MAX(experience) max_exp, MAX(alignment) max_align, MIN(alignment) min_alignment, MAX(kills) max_kills FROM player WHERE game_id = ' . $gameID . ' ORDER BY experience DESC');
if ($db->nextRecord()) {
	$template->assign('TotalPlayers', $db->getInt('total_players'));
	$template->assign('HighestExp', $db->getInt('max_exp'));
	$template->assign('HighestAlign', $db->getInt('max_align'));
	$template->assign('LowestAlign', $db->getInt('min_alignment'));
	$template->assign('HighestKills', $db->getInt('max_kills'));
}
	
$db->query('SELECT count(*) num_alliance FROM alliance WHERE game_id = ' . $gameID);
if ($db->nextRecord()) {
	$template->assign('TotalAlliances', $db->getInt('num_alliance'));
}

$db->query('SELECT * FROM player WHERE game_id = ' . $gameID . ' ORDER BY experience DESC LIMIT 10');
if ($db->getNumRows() > 0) {
	$rank = 0;
	$expRankings = array();
	while ($db->nextRecord()) {
		$expRankings[++$rank] = SmrPlayer::getPlayer($db->getInt('account_id'), $gameID, false, $db);
	}
	$template->assign('ExperienceRankings', $expRankings);
}


$db->query('SELECT * FROM player WHERE game_id = ' . $gameID . ' ORDER BY kills DESC LIMIT 10');
if ($db->getNumRows() > 0) {
	$rank = 0;
	$killRankings = array();
	while ($db->nextRecord()) {
		$killRankings[++$rank] = SmrPlayer::getPlayer($db->getInt('account_id'), $gameID, false, $db);
	}
	$template->assign('KillRankings', $killRankings);
}

function allianceTopTen($gameID, $field) {
	$db = new SmrMySqlDatabase();
	$db->query('SELECT alliance_id, SUM(' . $field . ') amount
				FROM alliance
				LEFT JOIN player USING (game_id, alliance_id)
				WHERE game_id = ' . $db->escapeNumber($gameID) . '
				GROUP BY alliance_id, alliance_name
				ORDER BY amount DESC, alliance_name
				LIMIT 10');
	$rankings = array();
	if ($db->getNumRows() > 0) {
		$rank = 0;
		while ($db->nextRecord()) {
			++$rank;
			$rankings[$rank]['Alliance'] = SmrAlliance::getAlliance($db->getInt('alliance_id'), $gameID);
			$rankings[$rank]['Amount'] = $db->getInt('amount');
		}
	}
	return $rankings;
}

$template->assign('AllianceExpRankings', allianceTopTen($gameID, 'experience'));
$template->assign('AllianceKillRankings', allianceTopTen($gameID, 'kills'));
