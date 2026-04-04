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
	$dbResult = $db->select('alliance_has_op', $player->getAlliance()->SQLID);
	if (!$dbResult->hasRecord()) {
		return ['Your leader has not scheduled an op.'];
	}

	$responses = [
		'YES' => [],
		'NO' => [],
		'MAYBE' => [],
	];
	$dbResult = $db->select(
		'alliance_has_op_response',
		$player->getAlliance()->SQLID,
		['account_id', 'response'],
	);
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
