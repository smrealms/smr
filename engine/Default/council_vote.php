<?php

if (!$player->isOnCouncil())
{
	create_error('You have to be on the council in order to vote.');
}
		
include(get_file_loc('race_voting.php'));
require_once(get_file_loc('council.inc'));
require_once(get_file_loc('menue.inc'));

$template->assign('PageTopic','Ruling Council Of '.$player->getRaceName());


$PHP_OUTPUT.=create_council_menue($player->getRaceID());

// determine for what we voted
$db->query('SELECT * FROM player_votes_relation ' .
		   'WHERE account_id = '.$player->getAccountID().' AND ' .
				 'game_id = '.$player->getGameID());
$voted_for_race = -1;
if ($db->nextRecord())
{
	$voted_for_race	= $db->getField('race_id_2');
	$voted_for		= $db->getField('action');
}

$PHP_OUTPUT.=('<table class="standard" align="center" width="75%">');
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<th>Race</th>');
$PHP_OUTPUT.=('<th>Vote</th>');
$PHP_OUTPUT.=('<th>Our Relation<br />with them</th>');
$PHP_OUTPUT.=('<th>Their Relation<br />with us</th>');
$PHP_OUTPUT.=('</tr>');
$playerRaceGlobalRelations = Globals::getRaceRelations($player->getGameID(),$player->getRaceID());
$races =& Globals::getRaces();
foreach($races as $raceID => $raceInfo)
{
	if($raceID == RACE_NEUTRAL || $raceID == $player->getRaceID())
		continue;

	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<td align="center">' . $player->getColouredRaceName($raceID) . '</td>');

	$container = array();
	$container['url']		= 'council_vote_processing.php';
	$container['race_id']	= $raceID;

	$PHP_OUTPUT.=create_echo_form($container);
	$PHP_OUTPUT.=('<td align="center">');
	if ($voted_for_race == $raceID && $voted_for == 'INC')
		$PHP_OUTPUT.=create_submit_style('Increase', 'background-color:green;');
	else
		$PHP_OUTPUT.=create_submit('Increase');
	$PHP_OUTPUT.=('&nbsp;');
	if ($voted_for_race == $raceID && $voted_for == 'DEC')
		$PHP_OUTPUT.=create_submit_style('Decrease', 'background-color:green;');
	else
		$PHP_OUTPUT.=create_submit('Decrease');
	$PHP_OUTPUT.=('</td>');
	$PHP_OUTPUT.=('</form>');

	$relation = $playerRaceGlobalRelations[$raceID];
	$PHP_OUTPUT.=('<td align="center">' . get_colored_text($relation, $relation) . '</td>');

	$otherRaceGlobalRelations = Globals::getRaceRelations($player->getGameID(),$raceID);
	$relation = $otherRaceGlobalRelations[$player->getRaceID()];
	$PHP_OUTPUT.=('<td align="center">' . get_colored_text($relation, $relation) . '</td>');

	$PHP_OUTPUT.=('</tr>');
}

$PHP_OUTPUT.=('</table>');

$PHP_OUTPUT.=('<p>&nbsp;</p>');

$db->query('SELECT * FROM race_has_voting ' .
		   'WHERE '.TIME.' < end_time AND ' .
				 'game_id = '.$player->getGameID().' AND ' .
				 'race_id_1 = '.$player->getRaceID());
if ($db->getNumRows() > 0)
{
	$PHP_OUTPUT.=('<table class="standard" align="center" width="65%">');
	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<th>Race</th>');
	$PHP_OUTPUT.=('<th>Treaty</th>');
	$PHP_OUTPUT.=('<th>Option</th>');
	$PHP_OUTPUT.=('<th>Currently</th>');
	$PHP_OUTPUT.=('<th>End Time</th>');
	$PHP_OUTPUT.=('</tr>');

	$db2 = new SmrMySqlDatabase();

	$president =& Council::getPresident($player->getGameID(),$player->getRaceID());
	while ($db->nextRecord())
	{
		$race_id_2	= $db->getField('race_id_2');
		$type		= $db->getField('type');
		$end_time	= $db->getField('end_time');

		$PHP_OUTPUT.=('<tr>');
		$PHP_OUTPUT.=('<td align="center">' . $player->getColouredRaceName($race_id_2) . '</td>');
		$PHP_OUTPUT.=('<td align="center">'.$type.'</td>');

		$container = array();
		$container['url']		= 'council_vote_processing.php';
		$container['race_id']	= $race_id_2;

		$PHP_OUTPUT.=create_echo_form($container);

		$db2->query('SELECT vote FROM player_votes_pact ' .
					'WHERE account_id = '.$player->getAccountID().' AND ' .
						  'game_id = '.$player->getGameID().' AND ' .
						  'race_id_1 = '.$player->getRaceID().' AND ' .
						  'race_id_2 = '.$race_id_2);
		if ($db2->nextRecord())
			$voted_for = $db2->getField('vote');
		else
			$voted_for = '';

		$PHP_OUTPUT.=('<td class="noWrap" align="center">');
		if ($voted_for == 'YES')
			$PHP_OUTPUT.=create_submit_style('Yes', 'background-color:green;');
		else
			$PHP_OUTPUT.=create_submit('Yes');
		$PHP_OUTPUT.=('&nbsp;');
		if ($voted_for == 'NO')
			$PHP_OUTPUT.=create_submit_style('No', 'background-color:green;');
		else
			$PHP_OUTPUT.=create_submit('No');
		if (is_object($president) && $player->equals($president))
		{
			$PHP_OUTPUT.=('&nbsp;');
			$PHP_OUTPUT.=create_submit('Veto');
		}
		$PHP_OUTPUT.=('</td>');

		// get 'yes' votes
		$db2->query('SELECT count(*) FROM player_votes_pact ' .
					'WHERE game_id = '.$player->getGameID().' AND ' .
						  'race_id_1 = '.$player->getRaceID().' AND ' .
						  'race_id_2 = '.$race_id_2.' AND ' .
						  'vote = \'YES\'');
		$db2->nextRecord();
		$yes_votes = $db2->getInt('count(*)');

		// get 'no' votes
		$db2->query('SELECT count(*) FROM player_votes_pact ' .
					'WHERE game_id = '.$player->getGameID().' AND ' .
						  'race_id_1 = '.$player->getRaceID().' AND ' .
						  'race_id_2 = '.$race_id_2.' AND ' .
						  'vote = \'NO\'');
		$db2->nextRecord();
		$no_votes = $db2->getInt('count(*)');

		$PHP_OUTPUT.=('<td align="center">'.$yes_votes.' / '.$no_votes.'</td>');
		$PHP_OUTPUT.=('<td class="noWrap" align="center">' . date(DATE_FULL_SHORT_SPLIT, $end_time) . '</td>');
		$PHP_OUTPUT.=('</form>');
		$PHP_OUTPUT.=('</tr>');
	}

	$PHP_OUTPUT.=('</table>');
}

?>