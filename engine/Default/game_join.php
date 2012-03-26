<?php

$game =& Globals::getGameInfo($var['game_id']);

$template->assignByRef('Game',$game);

// do we need credits for this game?
if ($game['GameCreditsRequired'] > 0) {
	// do we have enough
	if ($account->getTotalSmrCredits() < $game['GameCreditsRequired'])
		create_error('Sorry you do not have enough SMR Credits to play this game.<br />To get SMR credits you need to donate to SMR.');
}

// is the game already full?
$db->query('SELECT count(*) FROM player WHERE game_id = ' . $db->escapeNumber($var['game_id']));
if ($db->nextRecord() && $db->getInt('count(*)') >= $game['GameMaxPlayers']) {
	create_error('The maximum number of players in that game is reached!');
}

//if (TIME < $game['StartDate'])
//	create_error('You want to join a game that hasn\'t started yet?');

if (TIME > $game['EndDate'])
	create_error('You want to join a game that is already over?');

$template->assign('PageTopic', 'Join Game');

$raceInfo =& Globals::getRaces();
$raceDescriptions='';
$first = true;
foreach($raceInfo as $race) {
	if ($first) {
		$raceDescriptions.=('\'' . str_replace('\'','\\\'"',$race['Description']) . '\'');
		$first = false;

	}
	else
		$raceDescriptions.=(', \'' . str_replace('\'','\\\'',$race['Description']) . '\'');
}
$template->assign('RaceDescriptions',$raceDescriptions);


// create a container that will hold next url and additional variables.
$container = array();
$container['game_id'] = $var['game_id'];
$container['url'] = 'game_join_processing.php';
if (TIME >= $game['StartDate'])
	$template->assign('JoinGameFormHref',SmrSession::get_new_href($container));

$db2 = new SmrMySqlDatabase();
//this prevents multiple races appearing when there is more than 1 game
$only = array();
// get all available hq's
$db->query('SELECT location_name, location_type_id
			FROM location JOIN location_type USING(location_type_id)
			WHERE location_type_id > '.$db->escapeNumber(UNDERGROUND).'
				AND location_type_id < '.$db->escapeNumber(FED).'
				AND game_id = ' . $db->escapeNumber($var['game_id']) . '
			ORDER BY location_type_id');
$races = array();
while ($db->nextRecord()) {
	// get the name for this race
	// HACK! cut ' Headquarters' from location name!
	$race_name = substr(stripslashes($db->getField('location_name')), 0, -13);

	$curr_race_id = $db->getField('location_type_id') - 101;
	if (in_array($curr_race_id, $only)) continue;
	$only[] = $curr_race_id;
	// get number of traders in game
	$db2->query('SELECT count(*) as number_of_race FROM player WHERE race_id = '.$db2->escapeNumber($curr_race_id).' AND game_id = ' . $db2->escapeNumber($var['game_id']));
	$db2->nextRecord();

	$races[$curr_race_id]['ID'] = $curr_race_id;
	$races[$curr_race_id]['Name'] = $race_name;
	$races[$curr_race_id]['NumberOfPlayers'] = $db2->getInt('number_of_race');
	$races[$curr_race_id]['Selected'] = false;
}
if(count($races) > 1) {
	do {
		$raceKey = array_rand($races);
	} while($races[$raceKey]['ID'] == RACE_ALSKANT);
	$races[$raceKey]['Selected'] = true;
}
$template->assign('Races',$races);

?>