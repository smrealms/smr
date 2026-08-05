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

class ManageDraftLeadersProcessor extends AccountPageProcessor {

	private const string ACTION = 'action';

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
		$gameId = $this->selectedGameID;

		// Get the POST variables
		$playerId = Request::getInt('player_id');
		$homeSectorID = Request::getInt('home_sector_id');
		$action = Request::get(self::ACTION);

		try {
			$selectedPlayer = Player::getPlayerByPlayerID($playerId, $gameId);
		} catch (PlayerNotFound $e) {
			$msg = "<span class='red'>ERROR: </span>" . $e->getMessage();
			$container = new ManageDraftLeaders($this->selectedGameID, $msg);
			$container->go();
		}

		$name = $selectedPlayer->getDisplayName();
		$game = $selectedPlayer->getGame()->getDisplayName();

		$msg = null; // by default, clear any messages from prior processing
		if ($action === $this->actionAssign->value) {
			if ($selectedPlayer->isDraftLeader()) {
				$msg = "<span class='red'>ERROR: </span>$name is already a draft leader in game $game!";
			} else {
				$db->insert('draft_leaders', [
					...$selectedPlayer->SQLID,
					'home_sector_id' => $homeSectorID,
				]);
			}
		} elseif ($action === $this->actionRemove->value) {
			if (!$selectedPlayer->isDraftLeader()) {
				$msg = "<span class='red'>ERROR: </span>$name is not a draft leader in game $game!";
			} else {
				$db->delete('draft_leaders', $selectedPlayer->SQLID);
			}
		} else {
			throw new Exception('Unknown action: ' . $action);
		}

		$container = new ManageDraftLeaders($this->selectedGameID, $msg);
		$container->go();
	}

}
