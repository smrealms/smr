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
	$dbResult = $db->read('SELECT 1
				FROM alliance_has_op
				WHERE alliance_id = :alliance_id
					AND game_id = :game_id', [
		'alliance_id' => $db->escapeNumber($player->getAllianceID()),
		'game_id' => $db->escapeNumber($player->getGameID()),
	]);
	if (!$dbResult->hasRecord()) {
		return ['There is no op scheduled.'];
	}

	$oppers = [];
	$dbResult = $db->read('SELECT account_id
				FROM alliance_has_op_response
				WHERE alliance_id = :alliance_id
					AND game_id = :game_id
					AND response = \'YES\'', [
		'alliance_id' => $db->escapeNumber($player->getAllianceID()),
		'game_id' => $db->escapeNumber($player->getGameID()),
	]);
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

	if (empty($oppers)) {
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
