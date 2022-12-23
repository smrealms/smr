<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use AbstractSmrPlayer;
use Smr\Database;
use Smr\Page\PlayerPageProcessor;
use Smr\Request;

class TraderNoteAddProcessor extends PlayerPageProcessor {

	public function build(AbstractSmrPlayer $player): never {
		// Adds a new note into the database
		$note = Request::get('note');
		if (strlen($note) > 1000) {
			create_error('Note cannot be longer than 1000 characters.');
		}

		$note = htmlentities($note, ENT_QUOTES, 'utf-8');
		$note = nl2br($note);
		$db = Database::getInstance();
		$db->insert('player_has_notes', [
			'account_id' => $db->escapeNumber($player->getAccountID()),
			'game_id' => $db->escapeNumber($player->getGameID()),
			'note' => $db->escapeBinary(gzcompress($note)),
		]);

		(new TraderStatus())->go();
	}

}
