<?php

$template->assign('PageTopic', 'Search Trader Results');

$template->assign('Player', $player);

$player_id = SmrSession::getRequestVar('player_id');
$player_name = SmrSession::getRequestVar('player_name');
if (!is_numeric($player_id) && !empty($player_id)) {
	create_error('Please enter only numbers!');
}
if (empty($player_name) && empty($player_id)) {
	create_error('You must specify either a player name or ID!');
}

if (!empty($player_id)) {
	try {
		$resultPlayer = SmrPlayer::getPlayerByPlayerID($player_id, $player->getGameID());
	} catch (PlayerNotFoundException $e) {}
} else {
	$db->query('SELECT * FROM player
				WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
					AND player_name = ' . $db->escapeString($player_name) . ' LIMIT 1');
	if ($db->nextRecord()) {
		$resultPlayer = SmrPlayer::getPlayer($db->getInt('account_id'), $player->getGameID(), false, $db);
	}

	$db->query('SELECT * FROM player
				WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
					AND player_name LIKE ' . $db->escapeString('%'.$player_name.'%') . '
					AND player_name != ' . $db->escapeString($player_name) . '
				ORDER BY player_name LIMIT 5');
	$similarPlayers = array();
	while ($db->nextRecord()) {
		$similarPlayers[] = SmrPlayer::getPlayer($db->getInt('account_id'), $player->getGameID(), false, $db);
	}
}

function playerLinks(SmrPlayer $curr_player) {
	global $player;
	$result = array('Player' => $curr_player);

	$container = create_container('skeleton.php', 'trader_search_result.php');
	$container['player_id'] = $curr_player->getPlayerID();
	$result['SearchHREF'] = SmrSession::getNewHREF($container);

	$container = create_container('skeleton.php', 'council_list.php');
	$container['race_id'] = $curr_player->getRaceID();
	$result['RaceHREF'] = SmrSession::getNewHREF($container);

	$container = create_container('skeleton.php', 'message_send.php');
	$container['receiver'] = $curr_player->getAccountID();
	$result['MessageHREF'] = SmrSession::getNewHREF($container);

	$container = create_container('skeleton.php', 'bounty_view.php');
	$container['id'] = $curr_player->getAccountID();
	$result['BountyHREF'] = SmrSession::getNewHREF($container);

	$container = create_container('skeleton.php', 'hall_of_fame_player_detail.php');
	$container['account_id'] = $curr_player->getAccountID();
	$container['game_id'] = $curr_player->getGameID();
	$container['sending_page'] = 'search';
	$result['HofHREF'] = SmrSession::getNewHREF($container);

	$container = create_container('skeleton.php', 'news_read_advanced.php');
	$container['submit'] = 'Search For Player';
	$container['playerName'] = $curr_player->getPlayerName();
	$result['NewsHREF'] = SmrSession::getNewHREF($container);

	if (in_array($player->getAccountID(), Globals::getHiddenPlayers())) {
		$container= create_container('sector_jump_processing.php');
		$container['to'] = $curr_player->getSectorID();
		$result['JumpHREF'] = SmrSession::getNewHREF($container);
	}

	return $result;
}

if (!empty($resultPlayer)) {
	$resultPlayerLinks = playerLinks($resultPlayer);
	$template->assign('ResultPlayerLinks', $resultPlayerLinks);
}

if (!empty($similarPlayers)) {
	$similarPlayersLinks = array();
	foreach ($similarPlayers as $similarPlayer) {
		$similarPlayersLinks[] = playerLinks($similarPlayer);
	}
	$template->assign('SimilarPlayersLinks', $similarPlayersLinks);
}

if (empty($resultPlayer) && empty($similarPlayers)) {
	$container = create_container('skeleton.php', 'trader_search.php');
	$container['empty_result'] = true;
	forward($container);
}
