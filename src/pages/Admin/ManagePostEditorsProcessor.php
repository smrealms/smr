<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Exception;
use Smr\Account;
use Smr\Database;
use Smr\Exceptions\PlayerNotFound;
use Smr\Html\Submit;
use Smr\Page\AccountPageProcessor;
use Smr\Player;
use Smr\Request;

class ManagePostEditorsProcessor extends AccountPageProcessor {

	private const string ACTION = 'submit';

	public readonly Submit $actionAssign;
	public readonly Submit $actionRemove;

	public function __construct(
		private readonly int $selectedGameID,
	) {
		$this->actionAssign = new Submit(self::ACTION, 'Assign');
		$this->actionRemove = new Submit(self::ACTION, 'Remove');
	}

	public function build(Account $account): never {
		$db = Database::getInstance();

		// Get the selected game
		$game_id = $this->selectedGameID;

		// Get the POST variables
		$player_id = Request::getInt('player_id');
		$action = Request::get(self::ACTION);

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
		if ($action === $this->actionAssign->value) {
			if ($selected_player->isGPEditor()) {
				$msg = "<span class='red'>ERROR: </span>$name is already an editor in game $game!";
			} else {
				$db->insert('galactic_post_writer', $selected_player->SQLID);
			}
		} elseif ($action === $this->actionRemove->value) {
			if (!$selected_player->isGPEditor()) {
				$msg = "<span class='red'>ERROR: </span>$name is not an editor in game $game!";
			} else {
				$db->delete('galactic_post_writer', $selected_player->SQLID);
			}
		} else {
			throw new Exception('Unknown action: ' . $action);
		}

		$container = new ManagePostEditors($this->selectedGameID, $msg);
		$container->go();
	}

}
