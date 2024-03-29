<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Exception;
use Smr\AbstractPlayer;
use Smr\CombatLogType;
use Smr\Database;
use Smr\Page\PlayerPageProcessor;
use Smr\Request;

class CombatLogListProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly CombatLogType $action,
	) {}

	public function build(AbstractPlayer $player): never {
		// If here, we have hit either the 'Save', 'Delete', or 'View' form buttons.
		// Immediately return to the log list if we haven't selected any logs.
		$logIDs = array_keys(Request::getArray('id', []));
		if (count($logIDs) === 0) {
			$message = 'You must select at least one combat log!';
			$container = new CombatLogList($this->action, message: $message);
			$container->go();
		}

		// Do we need to save any logs (or delete any saved logs)?
		$submitAction = Request::get('action');
		if ($submitAction === 'Save' || $submitAction === 'Delete') {
			$db = Database::getInstance();
			if ($submitAction === 'Save') {
				//save the logs we checked
				// Query means people can only save logs that they are allowd to view.
				$changedRows = $db->write('INSERT IGNORE INTO player_saved_combat_logs (account_id, game_id, log_id)
							SELECT :account_id, :game_id, log_id
							FROM combat_logs
							WHERE log_id IN (:log_ids)
								AND game_id = :game_id
								AND (
									attacker_id = :account_id
									OR defender_id = :account_id
									OR (:alliance_id > 0 AND (
										attacker_alliance_id = :alliance_id
										OR defender_alliance_id = :alliance_id
									))
								)
							LIMIT :limit', [
					'account_id' => $db->escapeNumber($player->getAccountID()),
					'game_id' => $db->escapeNumber($player->getGameID()),
					'alliance_id' => $db->escapeNumber($player->getAllianceID()),
					'log_ids' => $db->escapeArray($logIDs),
					'limit' => count($logIDs),
				]);
			} else { // $submitAction == 'Delete'
				$changedRows = $db->write('DELETE FROM player_saved_combat_logs
							WHERE log_id IN (:log_ids)
								AND account_id = :account_id
								AND game_id = :game_id
							LIMIT :limit', [
					'log_ids' => $db->escapeArray($logIDs),
					'account_id' => $db->escapeNumber($player->getAccountID()),
					'game_id' => $db->escapeNumber($player->getGameID()),
					'limit' => count($logIDs),
				]);
			}

			// Now that the logs have been saved/deleted, go back to the log list
			$message = $submitAction . 'd ' . $changedRows . ' new logs.';
			$container = new CombatLogList($this->action, message: $message);
			$container->go();
		} elseif ($submitAction === 'View') {
			sort($logIDs);
			$container = new CombatLogViewer($logIDs);
			$container->go();
		}
		throw new Exception('Unknown action: ' . $submitAction);
	}

}
