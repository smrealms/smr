<?php declare(strict_types=1);

namespace Smr\Pages\Player\BetaFunctions;

use Smr\AbstractPlayer;
use Smr\Database;
use Smr\Request;

class SetPoliticalRelationsProcessor extends BetaFunctionsPageProcessor {

	public function buildBetaFunctionsProcessor(AbstractPlayer $player): void {
		$amount = Request::getInt('amount');
		$race = Request::getInt('race');
		if ($player->getRaceID() == $race) {
			create_error('You cannot change race relations with your own race.');
		}
		$db = Database::getInstance();
		$db->update(
			'race_has_relation',
			['relation' => $db->escapeNumber($amount)],
			[
				'race_id_1' => $db->escapeNumber($player->getRaceID()),
				'race_id_2' => $db->escapeNumber($race),
				'game_id' => $db->escapeNumber($player->getGameID()),
			],
		);
		$db->update(
			'race_has_relation',
			['relation' => $db->escapeNumber($amount)],
			[
				'race_id_1' => $db->escapeNumber($race),
				'race_id_2' => $db->escapeNumber($player->getRaceID()),
				'game_id' => $db->escapeNumber($player->getGameID()),
			],
		);
	}

}
