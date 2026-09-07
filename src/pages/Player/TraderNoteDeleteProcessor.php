<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Database;
use Smr\Page\PlayerPageProcessor;
use Smr\Player;
use Smr\Request;

class TraderNoteDeleteProcessor extends PlayerPageProcessor {

	public function build(Player $player): never {
		$note_ids = Request::getIntArray('note_id', []);
		if (count($note_ids) > 0) {
			$db = Database::getInstance();
			$db->write('DELETE FROM player_has_notes WHERE player_id = :player_id
							AND note_id IN (:note_ids)', [
				'player_id' => $db->escapeNumber($player->getPlayerID()),
				'note_ids' => $db->escapeArray($note_ids),
			]);
		}

		new TraderStatus()->go();
	}

}
