<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Database;
use Smr\Page\PlayerPageProcessor;
use Smr\Request;

class TraderNoteAddProcessor extends PlayerPageProcessor {

	public function build(AbstractPlayer $player): never {
		// Adds a new note into the database
		$note = Request::get('note');
		if (strlen($note) > 1000) {
			create_error('Note cannot be longer than 1000 characters.');
		}

		$db = Database::getInstance();
		$db->insert('player_has_notes', [
			'account_id' => $player->getAccountID(),
			'game_id' => $player->getGameID(),
			'note' => $db->escapeObject($note, true),
		]);

		(new TraderStatus())->go();
	}

}
