<?php

include(get_file_loc('race_voting.php'));
$action = $_REQUEST['action'];
$action = strtoupper($action);

if ($action == 'INCREASE')
	$action = 'INC';
elseif ($action == 'DECREASE')
	$action = 'DEC';

$race_id = $var['race_id'];

if ($action == 'INC' || $action == 'DEC')

	$db->query('REPLACE INTO player_votes_relation ' .
			   '(account_id, game_id, race_id_1, race_id_2, action, time) ' .
			   'VALUES('.$player->getAccountID().', '.$player->getGameID().', '.$player->getRaceID().', '.$race_id.', '.$db->escapeString($action).', '.TIME.')');

elseif ($action == 'YES' || $action == 'NO')

	$db->query('REPLACE INTO player_votes_pact ' .
			   '(account_id, game_id, race_id_1, race_id_2, vote) ' .
			   'VALUES('.$player->getAccountID().', '.$player->getGameID().', '.$player->getRaceID().', '.$race_id.', '.$db->escapeString($action).')');

elseif ($action == 'VETO') {

	// try to cancel both votings
	$db->query('DELETE FROM race_has_voting ' .
			   'WHERE game_id = '.$player->getGameID().' AND ' .
					 'race_id_1 = '.$player->getRaceID().' AND ' .
					 'race_id_2 = '.$race_id);
	$db->query('DELETE FROM player_votes_pact ' .
			   'WHERE game_id = '.$player->getGameID().' AND ' .
					 'race_id_1 = '.$player->getRaceID().' AND ' .
					 'race_id_2 = '.$race_id);
	$db->query('DELETE FROM race_has_voting ' .
			   'WHERE game_id = '.$player->getGameID().' AND ' .
					 'race_id_1 = '.$race_id.' AND ' .
					 'race_id_2 = '.$player->getRaceID());
	$db->query('DELETE FROM player_votes_pact ' .
			   'WHERE game_id = '.$player->getGameID().' AND ' .
					 'race_id_1 = '.$race_id.' AND ' .
					 'race_id_2 = '.$player->getRaceID());

}

forward(create_container('skeleton.php', 'council_vote.php'));

?>