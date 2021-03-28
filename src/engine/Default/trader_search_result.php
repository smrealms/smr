<?php declare(strict_types=1);

$session = SmrSession::getInstance();

$player_id = $session->getRequestVarInt('player_id');
// When clicking on a player name, only the 'player_id' is supplied
$player_name = $session->getRequestVar('player_name', '');

if (empty($player_name) && empty($player_id)) {
	create_error('You must specify either a player name or ID!');
}

if (!empty($player_id)) {
	try {
		$resultPlayer = SmrPlayer::getPlayerByPlayerID($player_id, $player->getGameID());
	} catch (PlayerNotFoundException $e) {
		// No player found, we'll return an empty result
	}
} else {
	$db->query('SELECT * FROM player
				WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
					AND player_name = ' . $db->escapeString($player_name) . ' LIMIT 1');
	if ($db->nextRecord()) {
		$resultPlayer = SmrPlayer::getPlayer($db->getInt('account_id'), $player->getGameID(), false, $db);
	}

	$db->query('SELECT * FROM player
				WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
					AND player_name LIKE ' . $db->escapeString('%' . $player_name . '%') . '
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

	$container = Page::create('skeleton.php', 'trader_search_result.php');
	$container['player_id'] = $curr_player->getPlayerID();
	$result['SearchHREF'] = $container->href();

	$container = Page::create('skeleton.php', 'council_list.php');
	$container['race_id'] = $curr_player->getRaceID();
	$result['RaceHREF'] = $container->href();

	$container = Page::create('skeleton.php', 'message_send.php');
	$container['receiver'] = $curr_player->getAccountID();
	$result['MessageHREF'] = $container->href();

	$container = Page::create('skeleton.php', 'bounty_view.php');
	$container['id'] = $curr_player->getAccountID();
	$result['BountyHREF'] = $container->href();

	$container = Page::create('skeleton.php', 'hall_of_fame_player_detail.php');
	$container['account_id'] = $curr_player->getAccountID();
	$container['game_id'] = $curr_player->getGameID();
	$container['sending_page'] = 'search';
	$result['HofHREF'] = $container->href();

	$container = Page::create('skeleton.php', 'news_read_advanced.php');
	$container['submit'] = 'Search For Player';
	$container['playerName'] = $curr_player->getPlayerName();
	$result['NewsHREF'] = $container->href();

	if (in_array($player->getAccountID(), Globals::getHiddenPlayers())) {
		$container = Page::create('sector_jump_processing.php');
		$container['to'] = $curr_player->getSectorID();
		$result['JumpHREF'] = $container->href();
	}

	return $result;
}

if (empty($resultPlayer) && empty($similarPlayers)) {
	$container = Page::create('skeleton.php', 'trader_search.php');
	$container['empty_result'] = true;
	$container->go();
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

$template->assign('PageTopic', 'Search For Trader Results');
$template->assign('Player', $player);
