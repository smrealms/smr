<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Database;
use Smr\Page\PlayerPageProcessor;
use Smr\Request;

class AllianceOpResponseProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly int $allianceID
	) {}

	public function build(AbstractPlayer $player): never {
		$response = strtoupper(Request::get('op_response'));

		$db = Database::getInstance();
		$db->replace('alliance_has_op_response', [
			'alliance_id' => $db->escapeNumber($this->allianceID),
			'game_id' => $db->escapeNumber($player->getGameID()),
			'account_id' => $db->escapeNumber($player->getAccountID()),
			'response' => $db->escapeString($response),
		]);

		(new AllianceMotd($this->allianceID))->go();
	}

}
