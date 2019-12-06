<?php declare(strict_types=1);

function shared_channel_msg_op_info($player) {
	// get the op from db
	$db = new SmrMySqlDatabase();
	$db->query('SELECT time FROM alliance_has_op WHERE alliance_id = ' . $db->escapeNumber($player->getAllianceID()) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()));
	if (!$db->nextRecord()) {
		return array('Your leader has not scheduled an operation.');
	}

	// check if the op has already started
	$opTime = $db->getInt('time');
	if ($opTime < time()) {
		return array('The op started ' . format_time(time() - $opTime, true) . ' ago!');
	}

	// function to return op info message for each player
	$getOpInfoMessage = function($player) use ($opTime) {
		// have we signed up?
		$db = new SmrMySqlDatabase();
		$db->query('SELECT response FROM alliance_has_op_response WHERE alliance_id = ' . $db->escapeNumber($player->getAllianceID()) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND account_id = ' . $db->escapeNumber($player->getAccountID()));
		if ($db->nextRecord()) {
			$msg = $player->getPlayerName() . ' is on the ' . $db->getField('response') . ' list.';
		} else {
			$msg = $player->getPlayerName() . ' has not signed up for this one.';
		}

		// note: `getTurns` only gives turns at last access time
		$turnsAtLastAccess = $player->getTurns();
		$turnsGainedUntilNow = $player->getTurnsGained(time(), true);
		$turnsGainedUntilOp = $player->getTurnsGained($opTime, true);
		$turnsGainedFromNowToOp = $turnsGainedUntilOp - $turnsGainedUntilNow;

		// We may already have already reached the turn cap
		$currentTurns = min($player->getMaxTurns(), $turnsAtLastAccess + $turnsGainedUntilNow);
		$opTurns = $currentTurns + $turnsGainedFromNowToOp;

		if ($opTurns >= $player->getMaxTurns()) {
			$msg .= ' They will have max turns by then. If they do not move they\'ll waste ' . ($opTurns - $player->getMaxTurns()) . ' turns.';
		} else {
			$msg .= ' They will have ' . $opTurns . ' turns by then.';
		}
		return $msg;
	};

	// Get op info for each player we have access to
	$result = array_map($getOpInfoMessage, $player->getSharingPlayers(true));

	// Prepend the time left until the op
	array_unshift($result, 'The next scheduled op is in ' . format_time($opTime - time(), true) . '.');

	return $result;
}
