<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Database;
use Smr\Page\AccountPageProcessor;
use Smr\Request;

class LogConsoleNotesProcessor extends AccountPageProcessor {

	/**
	 * @param array<int> $accountIDs
	 * @param array<int> $logTypeIDs
	 */
	public function __construct(
		private readonly array $accountIDs,
		private readonly array $logTypeIDs
	) {}

	public function build(Account $account): never {
		$db = Database::getInstance();

		foreach ($this->accountIDs as $account_id) {
			if (empty(Request::get('notes'))) {
				$db->delete('log_has_notes', [
					'account_id' => $db->escapeNumber($account_id),
				]);
			} else {
				$db->replace('log_has_notes', [
					'account_id' => $db->escapeNumber($account_id),
					'notes' => $db->escapeString(Request::get('notes')),
				]);
			}
		}

		$container = new LogConsoleDetail($this->accountIDs, $this->logTypeIDs);
		$container->go();
	}

}
