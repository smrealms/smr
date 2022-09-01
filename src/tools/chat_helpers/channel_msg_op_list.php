<?php declare(strict_types=1);

use Smr\Database;

/**
 * @return array<string>
 */
function shared_channel_msg_op_list(AbstractSmrPlayer $player): array {
	// get the op info from db
	$db = Database::getInstance();
	$dbResult = $db->read('SELECT 1
				FROM alliance_has_op
				WHERE alliance_id = ' . $db->escapeNumber($player->getAllianceID()) . '
					AND game_id = ' . $db->escapeNumber($player->getGameID()));
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
				WHERE alliance_id = ' . $db->escapeNumber($player->getAllianceID()) . '
					AND game_id = ' . $db->escapeNumber($player->getGameID()));
	foreach ($dbResult->records() as $dbRecord) {
		$respondingPlayer = SmrPlayer::getPlayer($dbRecord->getInt('account_id'), $player->getGameID(), true);
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

	if (!$results) {
		return ['No one has responded to the upcoming op.'];
	}

	return $results;
}
