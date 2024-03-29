<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Database;
use Smr\Exceptions\PlayerNotFound;
use Smr\Page\AccountPageProcessor;
use Smr\Player;
use Smr\Request;

class ManageDraftLeadersProcessor extends AccountPageProcessor {

	public function __construct(
		private readonly int $selectedGameID,
	) {}

	public function build(Account $account): never {
		$db = Database::getInstance();

		// Get the selected game
		$gameId = $this->selectedGameID;

		// Get the POST variables
		$playerId = Request::getInt('player_id');
		$homeSectorID = Request::getInt('home_sector_id');
		$action = Request::get('submit');

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
		if ($action === 'Assign') {
			if ($selectedPlayer->isDraftLeader()) {
				$msg = "<span class='red'>ERROR: </span>$name is already a draft leader in game $game!";
			} else {
				$db->insert('draft_leaders', [
					...$selectedPlayer->SQLID,
					'home_sector_id' => $homeSectorID,
				]);
			}
		} elseif ($action === 'Remove') {
			if (!$selectedPlayer->isDraftLeader()) {
				$msg = "<span class='red'>ERROR: </span>$name is not a draft leader in game $game!";
			} else {
				$db->delete('draft_leaders', $selectedPlayer->SQLID);
			}
		} else {
			$msg = "<span class='red'>ERROR: </span>Do not know action '$action'!";
		}

		$container = new ManageDraftLeaders($this->selectedGameID, $msg);
		$container->go();
	}

}
