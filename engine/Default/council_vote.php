<?php

if (!$player->isOnCouncil())
{
	create_error('You have to be on the council in order to vote.');
}

require_once(get_file_loc('council.inc'));
require_once(get_file_loc('menu.inc'));

$template->assign('PageTopic','Ruling Council Of '.$player->getRaceName());


create_council_menue($player->getRaceID());

// determine for what we voted
$db->query('SELECT * FROM player_votes_relation ' .
		   'WHERE account_id = '.$player->getAccountID().' AND ' .
				 'game_id = '.$player->getGameID());
$votedForRace = -1;
if ($db->nextRecord())
{
	$votedForRace	= $db->getField('race_id_2');
	$votedFor		= $db->getField('action');
}

$voteRelations = array();
$playerRaceGlobalRelations = Globals::getRaceRelations($player->getGameID(),$player->getRaceID());
$races =& Globals::getRaces();
foreach($races as $raceID => $raceInfo)
{
	if($raceID == RACE_NEUTRAL || $raceID == $player->getRaceID())
		continue;
	$container = create_container('council_vote_processing.php', '', array('race_id' => $raceID));
	$otherRaceGlobalRelations = Globals::getRaceRelations($player->getGameID(),$raceID);
	$voteRelations[$raceID] = array(
		'HREF' => SmrSession::get_new_href($container),
		'Increased' => $votedForRace == $raceID && $votedFor == 'INC',
		'Decreased' => $votedForRace == $raceID && $votedFor == 'DEC',
		'RelationToThem' => $playerRaceGlobalRelations[$raceID],
		'RelationToUs' => $otherRaceGlobalRelations[$player->getRaceID()]
	);
}
$template->assign('VoteRelations', $voteRelations);

$voteTreaties = array();
$db->query('SELECT * FROM race_has_voting ' .
		   'WHERE '.TIME.' < end_time AND ' .
				 'game_id = '.$player->getGameID().' AND ' .
				 'race_id_1 = '.$player->getRaceID());
if ($db->getNumRows() > 0)
{

	$db2 = new SmrMySqlDatabase();

	while ($db->nextRecord())
	{
		$otherRaceID = $db->getField('race_id_2');
		$container = create_container('council_vote_processing.php', '', array('race_id' => $otherRaceID));
		
		// get 'yes' votes
		$db2->query('SELECT count(*) FROM player_votes_pact ' .
					'WHERE game_id = '.$player->getGameID().' AND ' .
						  'race_id_1 = '.$player->getRaceID().' AND ' .
						  'race_id_2 = '.$otherRaceID.' AND ' .
						  'vote = \'YES\'');
		$db2->nextRecord();
		$yesVotes = $db2->getInt('count(*)');

		// get 'no' votes
		$db2->query('SELECT count(*) FROM player_votes_pact ' .
					'WHERE game_id = '.$player->getGameID().' AND ' .
						  'race_id_1 = '.$player->getRaceID().' AND ' .
						  'race_id_2 = '.$otherRaceID.' AND ' .
						  'vote = \'NO\'');
		$db2->nextRecord();
		$noVotes = $db2->getInt('count(*)');

		$db2->query('SELECT vote FROM player_votes_pact ' .
					'WHERE account_id = '.$player->getAccountID().' AND ' .
						  'game_id = '.$player->getGameID().' AND ' .
						  'race_id_1 = '.$player->getRaceID().' AND ' .
						  'race_id_2 = '.$otherRaceID);
		$votedFor = '';
		if ($db2->nextRecord())
			$votedFor = $db2->getField('vote');
		
		$voteTreaties[$otherRaceID] = array(
			'HREF' => SmrSession::get_new_href($container),
			'Type' => $db->getField('type'),
			'EndTime' => $db->getField('end_time'),
			'For' => $votedFor == 'YES',
			'Against' => $votedFor == 'NO',
			'NoVotes' => $noVotes,
			'YesVotes' => $yesVotes
		);
	}
}
$template->assign('VoteTreaties', $voteTreaties);

?>