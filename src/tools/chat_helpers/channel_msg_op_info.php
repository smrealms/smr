<?php declare(strict_types=1);

use Smr\AbstractPlayer;
use Smr\Database;

/**
 * @return array<string>
 */
function shared_channel_msg_op_info(AbstractPlayer $player): array {
	// get the op from db
	$db = Database::getInstance();
	$dbResult = $db->read('SELECT time FROM alliance_has_op WHERE alliance_id = :alliance_id AND game_id = :game_id', [
		'alliance_id' => $db->escapeNumber($player->getAllianceID()),
		'game_id' => $db->escapeNumber($player->getGameID()),
	]);
	if (!$dbResult->hasRecord()) {
		return ['Your leader has not scheduled an operation.'];
	}

	// check if the op has already started
	$opTime = $dbResult->record()->getInt('time');
	if ($opTime < time()) {
		return ['The op started ' . format_time(time() - $opTime, true) . ' ago!'];
	}

	// function to return op info message for each player
	$getOpInfoMessage = function(AbstractPlayer $player) use ($opTime): string {
		// have we signed up?
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT response FROM alliance_has_op_response WHERE alliance_id = :alliance_id AND ' . AbstractPlayer::SQL, [
			'alliance_id' => $db->escapeNumber($player->getAllianceID()),
			...$player->SQLID,
		]);
		if ($dbResult->hasRecord()) {
			$msg = $player->getPlayerName() . ' is on the ' . $dbResult->record()->getString('response') . ' list.';
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
	array_unshift($result, 'The next scheduled op is ' . in_time_or_now($opTime - time(), true) . '.');

	return $result;
}
