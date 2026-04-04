<?php declare(strict_types=1);

use Smr\AbstractPlayer;
use Smr\Database;
use Smr\Player;

/**
 * @return array<string>
 */
function shared_channel_msg_op_turns(AbstractPlayer $player): array {
	// get the op from db
	$db = Database::getInstance();
	$dbResult = $db->select('alliance_has_op', $player->getAlliance()->SQLID);
	if (!$dbResult->hasRecord()) {
		return ['There is no op scheduled.'];
	}

	$oppers = [];
	$dbResult = $db->select(
		'alliance_has_op_response',
		[...$player->getAlliance()->SQLID, 'response' => 'YES'],
		['account_id'],
	);
	foreach ($dbResult->records() as $dbRecord) {
		$attendeePlayer = Player::getPlayer($dbRecord->getInt('account_id'), $player->getGameID(), true);
		// check that the player is still in this alliance
		if (!$player->sameAlliance($attendeePlayer)) {
			continue;
		}
		$turns = min(
			$attendeePlayer->getTurns() + $attendeePlayer->getTurnsGained(time(), true),
			$attendeePlayer->getMaxTurns(),
		);
		$oppers[$attendeePlayer->getPlayerName()] = $turns;
	}

	if (count($oppers) === 0) {
		return ['There are no op participants.'];
	}

	// sort by turns
	arsort($oppers);

	// return result to channel
	$output = [];
	foreach ($oppers as $opper => $turns) {
		$output[] = "$turns : $opper";
	}
	return $output;
}
