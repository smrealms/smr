<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Exception;
use Smr\Account;
use Smr\Database;
use Smr\Game;
use Smr\Page\AccountPageProcessor;
use Smr\Request;

class GameDeleteProcessor extends AccountPageProcessor {

	public function __construct(
		private readonly int $deleteGameID,
	) {}

	public function build(Account $account): never {

		$delete = Request::getBool('action');

		$message = null;
		if ($delete) {
			$game = Game::getGame($this->deleteGameID);
			if ($game->isEnabled()) {
				throw new Exception('Cannot delete enabled game: ' . $this->deleteGameID);
			}

			// Since game is not enabled, we only need to remove it from tables
			// that are populated by the UniGen.
			$db = Database::getInstance();
			$data = ['game_id' => $this->deleteGameID];
			$db->write('DELETE FROM game WHERE game_id = :game_id', $data);
			$db->write('DELETE FROM game_galaxy WHERE game_id = :game_id', $data);
			$db->write('DELETE FROM location WHERE game_id = :game_id', $data);
			$db->write('DELETE FROM planet WHERE game_id = :game_id', $data);
			$db->write('DELETE FROM port WHERE game_id = :game_id', $data);
			$db->write('DELETE FROM port_has_goods WHERE game_id = :game_id', $data);
			$db->write('DELETE FROM race_has_relation WHERE game_id = :game_id', $data);
			$db->write('DELETE FROM sector WHERE game_id = :game_id', $data);

			$message = '<span class="green">SUCCESS: </span>deleted game: ' . $game->getDisplayName();
		}

		(new AdminTools(message: $message))->go();
	}

}
