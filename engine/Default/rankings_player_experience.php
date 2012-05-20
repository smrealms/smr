<?php

$template->assign('PageTopic','Experience Rankings');
$template->assign('RankingStat', 'Experience');

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

// how many players are there?
$db->query('SELECT count(*) FROM player WHERE game_id = ' . $db->escapeNumber($player->getGameID()));
$db->nextRecord();
$totalPlayers = $db->getInt('count(*)');
$template->assign('TotalPlayers', $totalPlayers);

$db->query('SELECT * FROM player WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' ORDER BY experience DESC, player_name LIMIT 10');
$rank = 0;

$rankings = array();
while ($db->nextRecord()) {
	// increase rank counter
	$rank++;
	$rankings[$rank] = array();
	$currentPlayer =& SmrPlayer::getPlayer($db->getField('account_id'), $player->getGameID());

	$class='';
	if ($player->equals($currentPlayer))
		$class .= 'bold';
	if($currentPlayer->getAccount()->isNewbie())
		$class.= ' newbie';
	if($class!='')
		$class = ' class="'.trim($class).'"';

	$rankings[$rank]['Player'] =& $currentPlayer;
	$rankings[$rank]['Class'] = $class;
	$rankings[$rank]['Value'] = number_format($currentPlayer->getExperience());
}
$template->assignByRef('Rankings', $rankings);

if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'Show' && is_numeric($_REQUEST['min_rank'])&&is_numeric($_REQUEST['max_rank'])) {
	$minRank = min($_REQUEST['min_rank'], $_REQUEST['max_rank']);
	$maxRank = max($_REQUEST['min_rank'], $_REQUEST['max_rank']);
	SmrSession::updateVar('MinRank',$minRank);
	SmrSession::updateVar('MaxRank',$maxRank);
}
elseif(isset($var['MinRank'])&&isset($var['MaxRank'])) {
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

if ($maxRank > $totalPlayers) {
	$maxRank = $totalPlayers;
}

$template->assign('MaxRank', $maxRank);
$template->assign('MinRank', $minRank);

$container = create_container('skeleton.php', 'rankings_player_experience.php');
$container['min_rank']	= $minRank;
$container['max_rank']	= $maxRank;
$template->assign('FilterRankingsHREF', SmrSession::getNewHREF($container));

$db->query('SELECT * FROM player WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' ORDER BY experience DESC, player_name LIMIT ' . ($minRank - 1) . ', ' . ($maxRank - $minRank + 1));
$rank = $minRank - 1;
$filteredRankings = array();
while ($db->nextRecord()) {
	// increase rank counter
	$rank++;
	$filteredRankings[$rank] = array();
	$currentPlayer =& SmrPlayer::getPlayer($db->getField('account_id'), $player->getGameID());

	$class='';
	if ($player->equals($currentPlayer))
		$class .= 'bold';
	if($currentPlayer->getAccount()->isNewbie())
		$class.= ' newbie';
	if($class!='')
		$class = ' class="'.trim($class).'"';

	$filteredRankings[$rank]['Player'] =& $currentPlayer;
	$filteredRankings[$rank]['Class'] = $class;
	$filteredRankings[$rank]['Value'] = number_format($currentPlayer->getExperience());
}
$template->assignByRef('FilteredRankings', $filteredRankings);
?>