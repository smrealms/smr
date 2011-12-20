<?php

//get game id
$gameID = $var['game_id'];

$template->assign('StatsGameID',$gameID);

$db->query('SELECT count(*) total_players, MAX(experience) max_exp, MAX(alignment) max_align, MIN(alignment) min_alignment, MAX(kills) max_kills FROM player WHERE game_id = '.$gameID.' ORDER BY experience DESC');
if ($db->nextRecord()) {
	$template->assign('TotalPlayers',$db->getField('total_players'));
	$template->assign('HighestExp',$db->getField('max_exp'));
	$template->assign('HighestAlign',$db->getField('max_align'));
	$template->assign('LowestAlign',$db->getField('min_alignment'));
	$template->assign('HighestKills',$db->getField('max_kills'));
}
	
$db->query('SELECT count(*) num_alliance FROM alliance WHERE game_id = '.$gameID);
if ($db->nextRecord()) {
	$template->assign('TotalAlliances',$db->getField('num_alliance'));
}

$db->query('SELECT account_id FROM player WHERE game_id = '.$gameID.' ORDER BY experience DESC LIMIT 10');
if ($db->getNumRows() > 0) {
	$rank = 0;
	$expRankings = array();
	while ($db->nextRecord()) {
		++$rank;
		$expRankings[$rank] =& SmrPlayer::getPlayer($db->getField('account_id'), $gameID);
	}
	$template->assign('ExperienceRankings',$expRankings);
}


$db->query('SELECT account_id FROM player WHERE game_id = '.$gameID.' ORDER BY kills DESC LIMIT 10');
if ($db->getNumRows() > 0) {
	$rank = 0;
	$killRankings = array();
	while ($db->nextRecord()) {
		++$rank;
		$killRankings[$rank] =& SmrPlayer::getPlayer($db->getField('account_id'), $gameID);
	}
	$template->assign('KillRankings',$killRankings);
}


$db->query('SELECT * FROM active_session
			WHERE last_accessed >= ' . (TIME - 600) . ' AND
				game_id = '.$gameID);
$count_real_last_active = $db->getNumRows();

$db->query('SELECT account_id FROM player ' .
		'WHERE last_cpl_action >= ' . (TIME - 600) . ' AND ' .
				'game_id = '.$gameID.' ' .
		'ORDER BY experience DESC, player_name');
$count_last_active = $db->getNumRows();

// fix it if some1 is using the logoff button
if ($count_real_last_active < $count_last_active)
	$count_real_last_active = $count_last_active;

$template->assign('PlayersAccessed',$count_real_last_active);

$currentPlayers = array();
while ($db->nextRecord()) {
	$currentPlayers[] =& SmrPlayer::getPlayer($db->getField('account_id'), $gameID);
}
$template->assign('CurrentPlayers',$currentPlayers);

?>