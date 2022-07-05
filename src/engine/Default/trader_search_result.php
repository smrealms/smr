<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$player = $session->getPlayer();

$player_id = $session->getRequestVarInt('player_id');
// When clicking on a player name, only the 'player_id' is supplied
$player_name = $session->getRequestVar('player_name', '');

if (empty($player_name) && empty($player_id)) {
	create_error('You must specify either a player name or ID!');
}

if (!empty($player_id)) {
	try {
		$resultPlayer = SmrPlayer::getPlayerByPlayerID($player_id, $player->getGameID());
	} catch (Smr\Exceptions\PlayerNotFound) {
		// No player found, we'll return an empty result
	}
} else {
	try {
		$resultPlayer = SmrPlayer::getPlayerByPlayerName($player_name, $player->getGameID());
	} catch (Smr\Exceptions\PlayerNotFound) {
		// No exact match, but that's okay
	}

	$db = Smr\Database::getInstance();
	$dbResult = $db->read('SELECT * FROM player
				WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
					AND player_name LIKE ' . $db->escapeString('%' . $player_name . '%') . '
					AND player_name != ' . $db->escapeString($player_name) . '
				ORDER BY player_name LIMIT 5');
	$similarPlayers = [];
	foreach ($dbResult->records() as $dbRecord) {
		$similarPlayers[] = SmrPlayer::getPlayer($dbRecord->getInt('account_id'), $player->getGameID(), false, $dbRecord);
	}
}

/**
 * @return array<string, SmrPlayer|string>
 */
function playerLinks(SmrPlayer $linkPlayer): array {
	$result = ['Player' => $linkPlayer];

	$container = Page::create('trader_search_result.php');
	$container['player_id'] = $linkPlayer->getPlayerID();
	$result['SearchHREF'] = $container->href();

	$container = Page::create('council_list.php');
	$container['race_id'] = $linkPlayer->getRaceID();
	$result['RaceHREF'] = $container->href();

	$container = Page::create('message_send.php');
	$container['receiver'] = $linkPlayer->getAccountID();
	$result['MessageHREF'] = $container->href();

	$container = Page::create('bounty_view.php');
	$container['id'] = $linkPlayer->getAccountID();
	$result['BountyHREF'] = $container->href();

	$container = Page::create('hall_of_fame_player_detail.php');
	$container['account_id'] = $linkPlayer->getAccountID();
	$container['game_id'] = $linkPlayer->getGameID();
	$container['sending_page'] = 'search';
	$result['HofHREF'] = $container->href();

	$container = Page::create('news_read_advanced.php');
	$container['submit'] = 'Search For Player';
	$container['playerName'] = $linkPlayer->getPlayerName();
	$result['NewsHREF'] = $container->href();

	$player = Smr\Session::getInstance()->getPlayer();
	if (in_array($player->getAccountID(), Globals::getHiddenPlayers())) {
		$container = Page::create('sector_jump_processing.php');
		$container['to'] = $linkPlayer->getSectorID();
		$result['JumpHREF'] = $container->href();
	}

	return $result;
}

if (empty($resultPlayer) && empty($similarPlayers)) {
	$container = Page::create('trader_search.php');
	$container['empty_result'] = true;
	$container->go();
}

if (!empty($resultPlayer)) {
	$resultPlayerLinks = playerLinks($resultPlayer);
	$template->assign('ResultPlayerLinks', $resultPlayerLinks);
}

if (!empty($similarPlayers)) {
	$similarPlayersLinks = [];
	foreach ($similarPlayers as $similarPlayer) {
		$similarPlayersLinks[] = playerLinks($similarPlayer);
	}
	$template->assign('SimilarPlayersLinks', $similarPlayersLinks);
}

$template->assign('PageTopic', 'Search For Trader Results');
$template->assign('Player', $player);
