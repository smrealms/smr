<?php declare(strict_types=1);

use Smr\AbstractPlayer;
use Smr\Database;
use Smr\Player;

/**
 * @return array<string>
 */
function shared_channel_msg_op_list(AbstractPlayer $player): array {
	// get the op info from db
	$db = Database::getInstance();
	$dbResult = $db->read('SELECT 1
				FROM alliance_has_op
				WHERE alliance_id = :alliance_id
					AND game_id = :game_id', [
		'alliance_id' => $db->escapeNumber($player->getAllianceID()),
		'game_id' => $db->escapeNumber($player->getGameID()),
	]);
	if (!$dbResult->hasRecord()) {
		return ['Your leader has not scheduled an op.'];
	}

	$responses = [
		'YES' => [],
		'NO' => [],
		'MAYBE' => [],
	];
	$dbResult = $db->read('SELECT account_id, response
				FROM alliance_has_op_response
				WHERE alliance_id = :alliance_id
					AND game_id = :game_id', [
		'alliance_id' => $db->escapeNumber($player->getAllianceID()),
		'game_id' => $db->escapeNumber($player->getGameID()),
	]);
	foreach ($dbResult->records() as $dbRecord) {
		$respondingPlayer = Player::getPlayer($dbRecord->getInt('account_id'), $player->getGameID(), true);
		// check that the player is still in this alliance
		if (!$player->sameAlliance($respondingPlayer)) {
			continue;
		}
		$responses[$dbRecord->getString('response')][] = $respondingPlayer;
	}

	$results = [];
	foreach ($responses as $response => $responders) {
		if (count($responders) > 0) {
			$results[] = $response . ' (' . count($responders) . '):';
			foreach ($responders as $responder) {
				$results[] = ' * ' . $responder->getPlayerName();
			}
		}
	}

	if (count($results) === 0) {
		return ['No one has responded to the upcoming op.'];
	}

	return $results;
}
