<?php

if (!$player->isOnCouncil()) {
	create_error('You have to be on the council in order to vote.');
}

$template->assign('PageTopic', 'Ruling Council Of ' . $player->getRaceName());
Menu::council($player->getRaceID());

// determine for what we voted
$db->query('SELECT * FROM player_votes_relation
			WHERE account_id = ' . $db->escapeNumber($player->getAccountID()) . '
				AND game_id = ' . $db->escapeNumber($player->getGameID()));
$votedForRace = -1;
if ($db->nextRecord()) {
	$votedForRace = $db->getField('race_id_2');
	$votedFor = $db->getField('action');
}

$voteRelations = array();
$globalRelations = Globals::getRaceRelations($player->getGameID(), $player->getRaceID());
foreach (Globals::getRaces() as $raceID => $raceInfo) {
	if ($raceID == RACE_NEUTRAL || $raceID == $player->getRaceID())
		continue;
	$container = create_container('council_vote_processing.php', '', array('race_id' => $raceID));
	$voteRelations[$raceID] = array(
		'HREF' => SmrSession::getNewHREF($container),
		'Increased' => $votedForRace == $raceID && $votedFor == 'INC',
		'Decreased' => $votedForRace == $raceID && $votedFor == 'DEC',
		'Relations' => $globalRelations[$raceID],
	);
}
$template->assign('VoteRelations', $voteRelations);

$voteTreaties = array();
$db->query('SELECT * FROM race_has_voting
			WHERE '.$db->escapeNumber(TIME) . ' < end_time
			AND game_id = ' . $db->escapeNumber($player->getGameID()) . '
			AND race_id_1 = ' . $db->escapeNumber($player->getRaceID()));
if ($db->getNumRows() > 0) {

	$db2 = new SmrMySqlDatabase();

	while ($db->nextRecord()) {
		$otherRaceID = $db->getField('race_id_2');
		$container = create_container('council_vote_processing.php', '', array('race_id' => $otherRaceID));

		// get 'yes' votes
		$db2->query('SELECT count(*) FROM player_votes_pact
					WHERE game_id = ' . $db2->escapeNumber($player->getGameID()) . '
						AND race_id_1 = ' . $db2->escapeNumber($player->getRaceID()) . '
						AND race_id_2 = ' . $db2->escapeNumber($otherRaceID) . '
						AND vote = \'YES\'');
		$db2->nextRecord();
		$yesVotes = $db2->getInt('count(*)');

		// get 'no' votes
		$db2->query('SELECT count(*) FROM player_votes_pact
					WHERE game_id = ' . $db2->escapeNumber($player->getGameID()) . '
						AND race_id_1 = ' . $db2->escapeNumber($player->getRaceID()) . '
						AND race_id_2 = ' . $db2->escapeNumber($otherRaceID) . '
						AND vote = \'NO\'');
		$db2->nextRecord();
		$noVotes = $db2->getInt('count(*)');

		$db2->query('SELECT vote FROM player_votes_pact
					WHERE account_id = ' . $db->escapeNumber($player->getAccountID()) . '
						AND game_id = ' . $db->escapeNumber($player->getGameID()) . '
						AND race_id_1 = ' . $db->escapeNumber($player->getRaceID()) . '
						AND race_id_2 = '.$db->escapeNumber($otherRaceID));
		$votedFor = '';
		if ($db2->nextRecord()) {
			$votedFor = $db2->getField('vote');
		}

		$voteTreaties[$otherRaceID] = array(
			'HREF' => SmrSession::getNewHREF($container),
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
