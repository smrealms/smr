<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$account = $session->getAccount();

$template->assign('PageTopic', 'Play Game');

if (isset($var['errorMsg'])) {
	$template->assign('ErrorMessage', $var['errorMsg']);
}
if (isset($var['msg'])) {
	$template->assign('Message', $var['msg']);
}

$template->assign('UserRankingLink', $account->getUserRankingHREF());
$template->assign('UserRankName', $account->getRankName());

// ***************************************
// ** Play Games
// ***************************************

$games = [];
$games['Play'] = [];
$game_id_list = [];
$db = Smr\Database::getInstance();
$dbResult = $db->read('SELECT end_time, game_id, game_name, game_speed, game_type
			FROM game JOIN player USING (game_id)
			WHERE account_id = ' . $db->escapeNumber($account->getAccountID()) . '
				AND enabled = \'TRUE\'
				AND end_time >= ' . $db->escapeNumber(Smr\Epoch::time()) . '
			ORDER BY start_time, game_id DESC');
foreach ($dbResult->records() as $dbRecord) {
	$game_id = $dbRecord->getInt('game_id');
	$games['Play'][$game_id]['ID'] = $game_id;
	$games['Play'][$game_id]['Name'] = $dbRecord->getField('game_name');
	$games['Play'][$game_id]['Type'] = SmrGame::GAME_TYPES[$dbRecord->getInt('game_type')];
	$games['Play'][$game_id]['EndDate'] = date($account->getDateTimeFormatSplit(), $dbRecord->getInt('end_time'));
	$games['Play'][$game_id]['Speed'] = $dbRecord->getFloat('game_speed');

	$container = Page::create('game_play_processing.php');
	$container['game_id'] = $game_id;
	$games['Play'][$game_id]['PlayGameLink'] = $container->href();

	// creates a new player object
	$curr_player = SmrPlayer::getPlayer($account->getAccountID(), $game_id);

	// update turns for this game
	$curr_player->updateTurns();

	// generate list of game_id that this player is joined
	$game_id_list[] = $game_id;

	$result2 = $db->read('SELECT count(*) as num_playing
					FROM player
					WHERE last_cpl_action >= ' . $db->escapeNumber(Smr\Epoch::time() - 600) . '
						AND game_id = ' . $db->escapeNumber($game_id));
	$games['Play'][$game_id]['NumberPlaying'] = $result2->record()->getInt('num_playing');

	// create a container that will hold next url and additional variables.

	$container_game = Page::create('skeleton.php', 'game_stats.php');
	$container_game['game_id'] = $game_id;
	$games['Play'][$game_id]['GameStatsLink'] = $container_game->href();
	$games['Play'][$game_id]['Turns'] = $curr_player->getTurns();
	$games['Play'][$game_id]['LastMovement'] = format_time(Smr\Epoch::time() - $curr_player->getLastActive(), true);

}

if (empty($games['Play'])) {
	unset($games['Play']);
}


// ***************************************
// ** Join Games
// ***************************************

if (count($game_id_list) > 0) {
	$dbResult = $db->read('SELECT game_id
				FROM game
				WHERE game_id NOT IN (' . $db->escapeArray($game_id_list) . ')
					AND end_time >= ' . $db->escapeNumber(Smr\Epoch::time()) . '
					AND enabled = ' . $db->escapeBoolean(true) . '
				ORDER BY start_time DESC');
} else {
	$dbResult = $db->read('SELECT game_id
				FROM game
				WHERE end_time >= ' . $db->escapeNumber(Smr\Epoch::time()) . '
					AND enabled = ' . $db->escapeBoolean(true) . '
				ORDER BY start_time DESC');
}

// are there any results?
foreach ($dbResult->records() as $dbRecord) {
	$game_id = $dbRecord->getInt('game_id');
	$game = SmrGame::getGame($game_id);
	$games['Join'][$game_id] = [
		'ID' => $game_id,
		'Name' => $game->getName(),
		'JoinTime' => $game->getJoinTime(),
		'StartDate' => date($account->getDateTimeFormatSplit(), $game->getStartTime()),
		'EndDate' => date($account->getDateTimeFormatSplit(), $game->getEndTime()),
		'Players' => $game->getTotalPlayers(),
		'Type' => $game->getGameType(),
		'Speed' => $game->getGameSpeed(),
		'Credits' => $game->getCreditsNeeded(),
	];
	// create a container that will hold next url and additional variables.
	$container = Page::create('skeleton.php', 'game_join.php');
	$container['game_id'] = $game_id;

	$games['Join'][$game_id]['JoinGameLink'] = $container->href();
}

// ***************************************
// ** Previous Games
// ***************************************

$games['Previous'] = [];

//New previous games
$dbResult = $db->read('SELECT start_time, end_time, game_name, game_type, game_speed, game_id ' .
		'FROM game WHERE enabled = \'TRUE\' AND end_time < ' . $db->escapeNumber(Smr\Epoch::time()) . ' ORDER BY game_id DESC');
foreach ($dbResult->records() as $dbRecord) {
	$game_id = $dbRecord->getInt('game_id');
	$games['Previous'][$game_id]['ID'] = $game_id;
	$games['Previous'][$game_id]['Name'] = $dbRecord->getField('game_name');
	$games['Previous'][$game_id]['StartDate'] = date($account->getDateFormat(), $dbRecord->getInt('start_time'));
	$games['Previous'][$game_id]['EndDate'] = date($account->getDateFormat(), $dbRecord->getInt('end_time'));
	$games['Previous'][$game_id]['Type'] = SmrGame::GAME_TYPES[$dbRecord->getInt('game_type')];
	$games['Previous'][$game_id]['Speed'] = $dbRecord->getFloat('game_speed');
	// create a container that will hold next url and additional variables.
	$container = Page::create('skeleton.php');
	$container['game_id'] = $container['GameID'] = $game_id;
	$container['game_name'] = $games['Previous'][$game_id]['Name'];

	$container['body'] = 'hall_of_fame_new.php';
	$games['Previous'][$game_id]['PreviousGameHOFLink'] = $container->href();
	$container['body'] = 'news_read.php';
	$games['Previous'][$game_id]['PreviousGameNewsLink'] = $container->href();
	$container['body'] = 'game_stats.php';
	$games['Previous'][$game_id]['PreviousGameLink'] = $container->href();
}

foreach (Globals::getHistoryDatabases() as $databaseName => $oldColumn) {
	//Old previous games
	$db->switchDatabases($databaseName);
	$dbResult = $db->read('SELECT start_date, end_date, game_name, type, speed, game_id
						FROM game ORDER BY game_id DESC');
	foreach ($dbResult->records() as $dbRecord) {
		$game_id = $dbRecord->getInt('game_id');
		$index = $databaseName . $game_id;
		$games['Previous'][$index]['ID'] = $game_id;
		$games['Previous'][$index]['Name'] = $dbRecord->getField('game_name');
		$games['Previous'][$index]['StartDate'] = date($account->getDateFormat(), $dbRecord->getInt('start_date'));
		$games['Previous'][$index]['EndDate'] = date($account->getDateFormat(), $dbRecord->getInt('end_date'));
		$games['Previous'][$index]['Type'] = $dbRecord->getField('type');
		$games['Previous'][$index]['Speed'] = $dbRecord->getFloat('speed');
		// create a container that will hold next url and additional variables.
		$container = Page::create('skeleton.php');
		$container['view_game_id'] = $game_id;
		$container['HistoryDatabase'] = $databaseName;
		$container['game_name'] = $games['Previous'][$index]['Name'];

		$container['body'] = 'history_games.php';
		$games['Previous'][$index]['PreviousGameLink'] = $container->href();
		$container['body'] = 'history_games_hof.php';
		$games['Previous'][$index]['PreviousGameHOFLink'] = $container->href();
		$container['body'] = 'history_games_news.php';
		$games['Previous'][$index]['PreviousGameNewsLink'] = $container->href();
		$container['body'] = 'history_games_detail.php';
		$games['Previous'][$index]['PreviousGameStatsLink'] = $container->href();
	}
}
$db->switchDatabaseToLive(); // restore database

$template->assign('Games', $games);

// ***************************************
// ** Voting
// ***************************************
$container = Page::create('skeleton.php', 'vote.php');
$template->assign('VotingHref', $container->href());

$dbResult = $db->read('SELECT * FROM voting WHERE end > ' . $db->escapeNumber(Smr\Epoch::time()) . ' ORDER BY end DESC');
if ($dbResult->hasRecord()) {
	$votedFor = [];
	$dbResult2 = $db->read('SELECT * FROM voting_results WHERE account_id = ' . $db->escapeNumber($account->getAccountID()));
	foreach ($dbResult2->records() as $dbRecord2) {
		$votedFor[$dbRecord2->getInt('vote_id')] = $dbRecord2->getInt('option_id');
	}
	$voting = [];
	foreach ($dbResult->records() as $dbRecord) {
		$voteID = $dbRecord->getInt('vote_id');
		$voting[$voteID]['ID'] = $voteID;
		$container = Page::create('vote_processing.php', 'game_play.php');
		$container['vote_id'] = $voteID;
		$voting[$voteID]['HREF'] = $container->href();
		$voting[$voteID]['Question'] = $dbRecord->getField('question');
		$voting[$voteID]['TimeRemaining'] = format_time($dbRecord->getInt('end') - Smr\Epoch::time(), true);
		$voting[$voteID]['Options'] = [];
		$dbResult2 = $db->read('SELECT option_id,text,count(account_id) FROM voting_options LEFT OUTER JOIN voting_results USING(vote_id,option_id) WHERE vote_id = ' . $db->escapeNumber($dbRecord->getInt('vote_id')) . ' GROUP BY option_id');
		foreach ($dbResult2->records() as $dbRecord2) {
			$voting[$voteID]['Options'][$dbRecord2->getInt('option_id')]['ID'] = $dbRecord2->getInt('option_id');
			$voting[$voteID]['Options'][$dbRecord2->getInt('option_id')]['Text'] = $dbRecord2->getField('text');
			$voting[$voteID]['Options'][$dbRecord2->getInt('option_id')]['Chosen'] = isset($votedFor[$dbRecord->getInt('vote_id')]) && $votedFor[$voteID] == $dbRecord2->getInt('option_id');
			$voting[$voteID]['Options'][$dbRecord2->getInt('option_id')]['Votes'] = $dbRecord2->getInt('count(account_id)');
		}
	}
	$template->assign('Voting', $voting);
}

// ***************************************
// ** Announcements View
// ***************************************
$container = Page::create('skeleton.php', 'announcements.php');
$container['view_all'] = 'yes';
$template->assign('OldAnnouncementsLink', $container->href());
