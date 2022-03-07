<?php declare(strict_types=1);

function shared_channel_msg_op_list(SmrPlayer $player): array {
	// get the op info from db
	$db = Smr\Database::getInstance();
	$dbResult = $db->read('SELECT 1
				FROM alliance_has_op
				WHERE alliance_id = ' . $db->escapeNumber($player->getAllianceID()) . '
					AND game_id = ' . $db->escapeNumber($player->getGameID()));
	if (!$dbResult->hasRecord()) {
		return ['Your leader has not scheduled an op.'];
	}

	$yes = [];
	$no = [];
	$maybe = [];
	$dbResult = $db->read('SELECT account_id, response
				FROM alliance_has_op_response
				WHERE alliance_id = ' . $db->escapeNumber($player->getAllianceID()) . '
					AND game_id = ' . $db->escapeNumber($player->getGameID()));
	foreach ($dbResult->records() as $dbRecord) {
		$respondingPlayer = SmrPlayer::getPlayer($dbRecord->getInt('account_id'), $player->getGameID(), true);
		// check that the player is still in this alliance
		if (!$player->sameAlliance($respondingPlayer)) {
			continue;
		}
		switch ($dbRecord->getField('response')) {
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
		return ['No one has signed up for the upcoming op.'];
	}

	$results = [];
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
