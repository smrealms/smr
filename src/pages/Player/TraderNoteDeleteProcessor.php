<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Database;
use Smr\Page\PlayerPageProcessor;
use Smr\Request;

class TraderNoteDeleteProcessor extends PlayerPageProcessor {

	public function build(AbstractPlayer $player): never {
		$note_ids = Request::getIntArray('note_id', []);
		if (!empty($note_ids)) {
			$db = Database::getInstance();
			$db->write('DELETE FROM player_has_notes WHERE game_id = :game_id
							AND account_id = :account_id
							AND note_id IN (:note_ids)', [
				'game_id' => $db->escapeNumber($player->getGameID()),
				'account_id' => $db->escapeNumber($player->getAccountID()),
				'note_ids' => $db->escapeArray($note_ids),
			]);
		}

		(new TraderStatus())->go();
	}

}
