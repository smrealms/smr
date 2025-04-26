<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Database;
use Smr\DatabaseResult;
use Smr\Epoch;
use Smr\Page\AccountPageProcessor;

class DatabaseCleanupProcessor extends AccountPageProcessor {

	public function __construct(
		private readonly string $action,
	) {}

	public function build(Account $account): never {
		// Get initial storage size
		$db = Database::getInstance();
		$initialBytes = $db->getDbBytes();

		$endedGameIDs = [];
		$dbResult = $db->read('SELECT game_id FROM game WHERE end_time < :now', [
			'now' => $db->escapeNumber(Epoch::time()),
		]);
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

		if ($this->action === 'delete') {
			$action = 'DELETE';
			$method = $db->write(...);
			$preview = false;
		} else {
			$action = 'SELECT COUNT(*)';
			$method = $db->read(...);
			$preview = true;
		}

		$numRows = function(DatabaseResult|int $result): int {
			if ($result instanceof DatabaseResult) {
				$result = $result->record()->getInt('COUNT(*)');
			}
			return $result;
		};

		$rowsDeleted = [];
		foreach ($tablesToClean as $table) {
			$result = $method($action . ' FROM ' . $table . ' WHERE game_id IN (:game_ids)', [
				'game_ids' => $db->escapeArray($endedGameIDs),
			]);
			$rowsDeleted[$table] = $numRows($result);
		}

		$result = $method($action . ' FROM npc_logs');
		$rowsDeleted['npc_logs'] = $numRows($result);

		// Get difference in storage size
		$diffBytes = $initialBytes - $db->getDbBytes();

		$results = [
			'preview' => $preview,
			'rowsDeleted' => $rowsDeleted,
			'diffBytes' => $diffBytes,
			'endedGameIDs' => $endedGameIDs,
		];
		$container = new DatabaseCleanup($results);
		$container->go();
	}

}
