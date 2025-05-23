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
			$data = $game->SQLID;
			$db->delete('game', $data);
			$db->delete('game_create_status', $data);
			$db->delete('game_galaxy', $data);
			$db->delete('location', $data);
			$db->delete('planet', $data);
			$db->delete('port', $data);
			$db->delete('port_has_goods', $data);
			$db->delete('race_has_relation', $data);
			$db->delete('sector', $data);

			$message = '<span class="green">SUCCESS: </span>deleted game: ' . $game->getDisplayName();
		}

		(new AdminTools(message: $message))->go();
	}

}
