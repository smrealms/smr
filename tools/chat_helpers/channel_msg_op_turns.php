<?php declare(strict_types=1);

function shared_channel_msg_op_turns($player) {
	// get the op from db
	$db = new SmrMySqlDatabase();
	$db->query('SELECT 1
				FROM alliance_has_op
				WHERE alliance_id = ' . $db->escapeNumber($player->getAllianceID()) . '
					AND game_id = ' . $db->escapeNumber($player->getGameID()));
	if (!$db->nextRecord()) {
		return array('There is no op scheduled.');
	}

	$oppers = array();
	$db->query('SELECT account_id
				FROM alliance_has_op_response
				WHERE alliance_id = ' . $db->escapeNumber($player->getAllianceID()) . '
					AND game_id = ' . $db->escapeNumber($player->getGameID()) . '
					AND response = \'YES\'');
	while ($db->nextRecord()) {
		$attendeePlayer = SmrPlayer::getPlayer($db->getInt('account_id'), $player->getGameID(), true);
		// check that the player is still in this alliance
		if (!$player->sameAlliance($attendeePlayer)) {
			continue;
		}
		$turns = min($attendeePlayer->getTurns() + $attendeePlayer->getTurnsGained(time(), true),
		             $attendeePlayer->getMaxTurns());
		$oppers[$attendeePlayer->getPlayerName()] = $turns;
	}

	if (empty($oppers)) {
		return array('There are no op participants.');
	}

	// sort by turns
	arsort($oppers);

	// return result to channel
	$output = array();
	foreach ($oppers as $opper => $turns) {
		$output[] = "$turns : $opper";
	}
	return $output;
}
