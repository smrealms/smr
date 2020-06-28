<?php declare(strict_types=1);

$game = SmrGame::getGame($var['game_id']);

// do we need credits for this game?
if ($game->getCreditsNeeded() > 0) {
	// do we have enough
	if ($account->getTotalSmrCredits() < $game->getCreditsNeeded()) {
		create_error('Sorry you do not have enough SMR Credits to play this game.<br />To get SMR credits you need to donate to SMR.');
	}
}

// is the game already full?
if ($game->getTotalPlayers() >= $game->getMaxPlayers()) {
	create_error('The maximum number of players in that game is reached!');
}

if ($game->hasEnded()) {
	create_error('You want to join a game that is already over?');
}

$template->assign('PageTopic', 'Join Game: ' . $game->getDisplayName());
$template->assign('Game', $game);

if (TIME >= $game->getJoinTime()) {
	$container = create_container('game_join_processing.php');
	transfer('game_id');
	$template->assign('JoinGameFormHref', SmrSession::getNewHREF($container));
}

$races = [];
foreach ($game->getPlayableRaceIDs() as $raceID) {
	// get number of traders in game
	$db->query('SELECT count(*) as number_of_race FROM player WHERE race_id = ' . $db->escapeNumber($raceID) . ' AND game_id = ' . $db->escapeNumber($var['game_id']));
	$db->requireRecord();

	$race = Globals::getRaces()[$raceID];
	$races[$raceID] = [
		'ID' => $raceID,
		'Name' => $race['Race Name'],
		'Description' => $race['Description'],
		'NumberOfPlayers' => $db->getInt('number_of_race'),
		'Selected' => false,
	];
}
if (empty($races)) {
	create_error('This game has no races assigned yet!');
}

// Pick an initial race to display (prefer *not* Alskant)
do {
	$raceKey = array_rand($races);
} while ($raceKey == RACE_ALSKANT && count($races) > 1);
$races[$raceKey]['Selected'] = true;
$template->assign('SelectedRaceID', $raceKey);
$template->assign('Races', $races);
