<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use AbstractSmrPlayer;
use Smr\Database;
use Smr\Page\PlayerPageProcessor;
use Smr\Request;

class TraderNoteDeleteProcessor extends PlayerPageProcessor {

	public function build(AbstractSmrPlayer $player): never {
		$note_ids = Request::getIntArray('note_id', []);
		if (!empty($note_ids)) {
			$db = Database::getInstance();
			$db->write('DELETE FROM player_has_notes WHERE game_id=' . $db->escapeNumber($player->getGameID()) . '
							AND account_id=' . $db->escapeNumber($player->getAccountID()) . '
							AND note_id IN (' . $db->escapeArray($note_ids) . ')');
		}

		(new TraderStatus())->go();
	}

}
