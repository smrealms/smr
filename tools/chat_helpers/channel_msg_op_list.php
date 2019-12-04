<?php declare(strict_types=1);

function shared_channel_msg_op_list($player) {
	// get the op info from db
	$db = new SmrMySqlDatabase();
	$db->query('SELECT 1
				FROM alliance_has_op
				WHERE alliance_id = ' . $db->escapeNumber($player->getAllianceID()) . '
					AND game_id = ' . $db->escapeNumber($player->getGameID()));
	if (!$db->nextRecord()) {
		return array('Your leader has not scheduled an op.');
	}

	$yes = array();
	$no = array();
	$maybe = array();
	$db->query('SELECT account_id, response
				FROM alliance_has_op_response
				WHERE alliance_id = ' . $db->escapeNumber($player->getAllianceID()) . '
					AND game_id = ' . $db->escapeNumber($player->getGameID()));
	while ($db->nextRecord()) {
		$respondingPlayer = SmrPlayer::getPlayer($db->getInt('account_id'), $player->getGameID(), true);
		// check that the player is still in this alliance
		if (!$player->sameAlliance($respondingPlayer)) {
			continue;
		}
		switch ($db->getField('response')) {
			case 'YES':
				$yes[] = $respondingPlayer;
			break;
			case 'NO':
				$no[] = $respondingPlayer;
			break;
			case 'MAYBE':
				$maybe[] = $respondingPlayer;
			break;
		}
	}

	if ((count($yes) + count($no) + count($maybe)) == 0) {
		return array('No one has signed up for the upcoming op.');
	}

	$results = array();
	if (count($yes) > 0) {
		$results[] = 'YES (' . count($yes) . '):';
		foreach ($yes as $attendee) {
			$results[] = ' * ' . $attendee->getPlayerName();
		}
	}

	if (count($maybe) > 0) {
		$results[] = 'MAYBE (' . count($maybe) . '):';
		foreach ($maybe as $attendee) {
			$results[] = ' * ' . $attendee->getPlayerName();
		}
	}

	if (count($no) > 0) {
		$results[] = 'NO (' . count($no) . '):';
		foreach ($no as $attendee) {
			$results[] = ' * ' . $attendee->getPlayerName();
		}
	}

	return $results;
}
