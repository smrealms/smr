<?php declare(strict_types=1);

$db = Smr\Database::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();

if (!$player->isOnCouncil()) {
	create_error('You have to be on the council in order to vote.');
}

$action = strtoupper(Request::get('action'));

if ($action == 'INCREASE') {
	$action = 'INC';
} elseif ($action == 'DECREASE') {
	$action = 'DEC';
}

$race_id = $var['race_id'];

if ($action == 'INC' || $action == 'DEC') {
	$db->write('REPLACE INTO player_votes_relation
				(account_id, game_id, race_id_1, race_id_2, action, time)
				VALUES(' . $db->escapeNumber($player->getAccountID()) . ', ' . $db->escapeNumber($player->getGameID()) . ', ' . $db->escapeNumber($player->getRaceID()) . ', ' . $db->escapeNumber($race_id) . ', ' . $db->escapeString($action) . ', ' . $db->escapeNumber(Smr\Epoch::time()) . ')');
} elseif ($action == 'YES' || $action == 'NO') {
	$db->write('REPLACE INTO player_votes_pact
			(account_id, game_id, race_id_1, race_id_2, vote)
			VALUES(' . $db->escapeNumber($player->getAccountID()) . ', ' . $db->escapeNumber($player->getGameID()) . ', ' . $db->escapeNumber($player->getRaceID()) . ', ' . $db->escapeNumber($race_id) . ', ' . $db->escapeString($action) . ')');
} elseif ($action == 'VETO') {
	// try to cancel both votings
	$db->write('DELETE FROM race_has_voting ' .
			'WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
				AND race_id_1 = ' . $db->escapeNumber($player->getRaceID()) . '
				AND race_id_2 = '.$db->escapeNumber($race_id));
	$db->write('DELETE FROM player_votes_pact ' .
			'WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
				AND race_id_1 = ' . $db->escapeNumber($player->getRaceID()) . '
				AND race_id_2 = '.$db->escapeNumber($race_id));
	$db->write('DELETE FROM race_has_voting ' .
			'WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
				AND race_id_1 = '.$db->escapeNumber($race_id) . '
				AND race_id_2 = ' . $db->escapeNumber($player->getRaceID()));
	$db->write('DELETE FROM player_votes_pact ' .
			'WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
				AND race_id_1 = '.$db->escapeNumber($race_id) . '
				AND race_id_2 = ' . $db->escapeNumber($player->getRaceID()));

}

Page::create('skeleton.php', 'council_vote.php')->go();
