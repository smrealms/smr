<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Database;
use Smr\Exceptions\PlayerNotFound;
use Smr\Page\AccountPageProcessor;
use Smr\Player;
use Smr\Request;

class ManagePostEditorsProcessor extends AccountPageProcessor {

	public function __construct(
		private readonly int $selectedGameID
	) {}

	public function build(Account $account): never {
		$db = Database::getInstance();

		// Get the selected game
		$game_id = $this->selectedGameID;

		// Get the POST variables
		$player_id = Request::getInt('player_id');
		$action = Request::get('submit');

		try {
			$selected_player = Player::getPlayerByPlayerID($player_id, $game_id);
		} catch (PlayerNotFound $e) {
			$msg = "<span class='red'>ERROR: </span>" . $e->getMessage();
			$container = new ManagePostEditors($this->selectedGameID, $msg);
			$container->go();
		}

		$name = $selected_player->getDisplayName();
		$game = $selected_player->getGame()->getDisplayName();

		$msg = null; // by default, clear any messages from prior processing
		if ($action == 'Assign') {
			if ($selected_player->isGPEditor()) {
				$msg = "<span class='red'>ERROR: </span>$name is already an editor in game $game!";
			} else {
				$db->insert('galactic_post_writer', $selected_player->SQLID);
			}
		} elseif ($action == 'Remove') {
			if (!$selected_player->isGPEditor()) {
				$msg = "<span class='red'>ERROR: </span>$name is not an editor in game $game!";
			} else {
				$db->write('DELETE FROM galactic_post_writer WHERE ' . Player::SQL, $selected_player->SQLID);
			}
		} else {
			$msg = "<span class='red'>ERROR: </span>Do not know action '$action'!";
		}

		$container = new ManagePostEditors($this->selectedGameID, $msg);
		$container->go();
	}

}
