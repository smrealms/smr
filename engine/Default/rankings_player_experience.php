<?php

$template->assign('PageTopic','Experience Rankings');

require_once(get_file_loc('menu.inc'));
create_ranking_menu(0, 0);

// what rank are we?
$db->query('SELECT count(*) FROM player WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
			AND (
				experience > '.$db->escapeNumber($player->getExperience()).'
				OR (
					experience = '.$db->escapeNumber($player->getExperience()).'
					AND player_name <= ' . $db->escapeString($player->getPlayerName()) . '
				)
			)');
$db->nextRecord();
$ourRank = $db->getInt('count(*)');
$template->assign('OurRank', $ourRank);

$totalPlayers = $player->getGame()->getTotalPlayers();
$template->assign('TotalPlayers', $totalPlayers);

$db->query('SELECT account_id, experience value FROM player WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' ORDER BY experience DESC, player_name LIMIT 10');
$template->assignByRef('Rankings', collectRankings($db, 0));

calculateMinMaxRanks($ourRank, $totalPlayers);
$template->assign('MaxRank', $var['MaxRank']);
$template->assign('MinRank', $var['MinRank']);

$container = create_container('skeleton.php', 'rankings_player_experience.php');
$template->assign('FilterRankingsHREF', SmrSession::getNewHREF($container));

$lowerLimit = $var['MinRank'] - 1;
$db->query('SELECT account_id, experience value FROM player WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' ORDER BY experience DESC, player_name LIMIT ' . $lowerLimit . ', ' . ($var['MaxRank'] - $lowerLimit));
$template->assignByRef('FilteredRankings', collectRankings($db, $lowerLimit));

function collectRankings(&$db, $player, $rank) {
	$rankings = array();
	while ($db->nextRecord()) {
		// increase rank counter
		$rank++;
		$currentPlayer =& SmrPlayer::getPlayer($db->getInt('account_id'), $player->getGameID());

		$class='';
		if ($player->equals($currentPlayer)) {
			$class .= 'bold';
		}
		if($currentPlayer->getAccount()->isNewbie()) {
			$class.= ' newbie';
		}
		if($class!='') {
			$class = ' class="'.trim($class).'"';
		}

		$rankings[$rank] = array(
			'Rank' => $rank,
			'Player' => &$currentPlayer,
			'Class' => $class,
			'Value' => number_format($db->getInt('value'))
		);
	}
	return $rankings;
}

function calculateMinMaxRanks($ourRank, $totalPlayers) {
	if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'Show' && is_numeric($_REQUEST['min_rank']) && is_numeric($_REQUEST['max_rank'])) {
		$minRank = min($_REQUEST['min_rank'], $_REQUEST['max_rank']);
		$maxRank = max($_REQUEST['min_rank'], $_REQUEST['max_rank']);
	}
	elseif(isset($var['MinRank']) && isset($var['MaxRank'])) {
		$minRank = $var['MinRank'];
		$maxRank = $var['MaxRank'];
	}
	else {
		$minRank = $ourRank - 5;
		$maxRank = $ourRank + 5;
	}

	if ($minRank <= 0 || $minRank > $totalPlayers) {
		$minRank = 1;
		$maxRank = 10;
	}

	$maxRank = min($maxRank, $totalPlayers);

	SmrSession::updateVar('MinRank',$minRank);
	SmrSession::updateVar('MaxRank',$maxRank);
}
?>