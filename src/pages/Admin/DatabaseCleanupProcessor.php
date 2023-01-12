<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Database;
use Smr\Epoch;
use Smr\Page\AccountPageProcessor;

class DatabaseCleanupProcessor extends AccountPageProcessor {

	public function __construct(
		private readonly string $action
	) {}

	public function build(Account $account): never {
		// Get initial storage size
		$db = Database::getInstance();
		$initialBytes = $db->getDbBytes();

		$endedGameIDs = [];
		$dbResult = $db->read('SELECT game_id FROM game WHERE end_time < ' . $db->escapeNumber(Epoch::time()));
		foreach ($dbResult->records() as $dbRecord) {
			$endedGameIDs[] = $dbRecord->getInt('game_id');
		}
		rsort($endedGameIDs);

		$tablesToClean = [
			'combat_logs',
			'message',
			'player_visited_port',
			'player_visited_sector',
			'port_info_cache',
			'player_has_unread_messages',
			'player_read_thread',
			'route_cache',
			'weighted_random',
		];

		if ($this->action == 'delete') {
			$action = 'DELETE';
			$method = 'write';
		} else {
			$action = 'SELECT 1';
			$method = 'read';
		}

		$rowsDeleted = [];
		foreach ($tablesToClean as $table) {
			$db->$method($action . ' FROM ' . $table . ' WHERE game_id IN (' . $db->escapeArray($endedGameIDs) . ')');
			$rowsDeleted[$table] = $db->getChangedRows();
		}

		$db->$method($action . ' FROM npc_logs');
		$rowsDeleted['npc_logs'] = $db->getChangedRows();

		// Get difference in storage size
		$diffBytes = $initialBytes - $db->getDbBytes();

		$container = new DatabaseCleanup($this->action, $rowsDeleted, $diffBytes, $endedGameIDs);
		$container->go();
	}

}
