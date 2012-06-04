<?php
$db->query('SELECT * FROM game WHERE game_id = ' . $var['game_id']);
$game = array();
if ($db->nextRecord())
{
	$game['ID'] = $db->getField('game_id');
	$game['Name'] = $db->getField('game_name');
	$game['StartDate'] = $db->getField('start_date');
	$game['EndDate'] = $db->getField('end_date');
	$game['MaxPlayers'] = $db->getField('max_players');
	$game['Type'] = $db->getField('max_players');
	$game['Speed'] = $db->getField('credits_needed');
	$game['Credits'] = $db->getField('credits_needed');
	$game['Description'] = $db->getField('game_description');
}

$template->assign('Game',$game);


// do we need credits for this game?
if ($game['Credits'] > 0)
{
	// do we have enough
	if ($account->getTotalSmrCredits() < $game['Credits'])
	{
	    create_error('Sorry you dont have enough SMR Credits to play this game.<br />To get SMR credits you need to donate to SMR');
	    return;
	}
}

// is the game already full?
$db->query('SELECT * FROM player WHERE game_id = ' . $var['game_id']);
if ($db->getNumRows() >= $game['MaxPlayers']) {

    create_error('The maximum number of players in that game is reached!');
    return;

}

if (TIME < $game['StartDate']) {

    create_error('You want to join a game that hasn\'t started yet?');
    return;

}

if (TIME > $game['EndDate']) {

    create_error('You want to join a game that is already over?');
    return;

}

$template->assign('PageTopic', 'Join Game');

$raceInfo =& Globals::getRaces();
$raceDescriptions='';
$first = true;
foreach($raceInfo as $race)
{
    if ($first)
    {
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
$template->assign('JoinGameFormHref',SmrSession::get_new_href($container));



$db2 = new SmrMySqlDatabase();
//this prevents multiple races appearing when there is more than 1 game
$only = array();
// get all available hq's
$db->query('SELECT location_name, location.location_type_id as loc_id
			FROM location NATURAL JOIN location_type
			WHERE location.location_type_id > '.UNDERGROUND.' AND
				  location.location_type_id < '.FED.' AND
				  game_id = ' . $var['game_id'] . '
			ORDER BY location.location_type_id');
$races = array();
while ($db->nextRecord())
{

	// get the name for this race
	// HACK! cut ' Headquarters' from location name!
	$race_name = substr(stripslashes($db->getField('location_name')), 0, -13);

	$curr_race_id = $db->getField('loc_id') - 101;
	if (in_array($curr_race_id, $only)) continue;
	$only[] = $curr_race_id;
	// get number of traders in game
	$db2->query('SELECT count(*) as number_of_race FROM player WHERE race_id = '.$curr_race_id.' AND game_id = ' . $var['game_id']);
	$db2->nextRecord();
	
	$races[$curr_race_id]['ID'] = $curr_race_id;
	$races[$curr_race_id]['Name'] = $race_name;
	$races[$curr_race_id]['NumberOfPlayers'] = $db2->getField('number_of_race')>0?$db2->getField('number_of_race'):0;
	
}
$template->assign('Races',$races);

?>
