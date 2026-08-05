<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Database;
use Smr\Html\Submit;
use Smr\Page\AccountPageProcessor;
use Smr\Request;

class LogConsoleProcessor extends AccountPageProcessor {

	private const string ACTION = 'action';

	public readonly Submit $actionView;
	public readonly Submit $actionDelete;

	public function __construct() {
		$this->actionView = new Submit(self::ACTION, 'View');
		$this->actionDelete = new Submit(self::ACTION, 'Delete');
	}

	public function build(Account $account): never {
		$accountIDs = Request::getIntArray('account_ids');
		// nothing marked?
		if (count($accountIDs) === 0) {
			create_error('You have to select the log files you want to view/delete!');
		}

		$db = Database::getInstance();
		$action = Request::get(self::ACTION);
		if ($action === $this->actionDelete->value) {
			// get rid of all entries
			$db->write('DELETE FROM account_has_logs WHERE account_id IN (:account_ids)', [
				'account_ids' => $db->escapeArray($accountIDs),
			]);
			$db->write('DELETE FROM log_has_notes WHERE account_id IN (:account_ids)', [
				'account_ids' => $db->escapeArray($accountIDs),
			]);
			$container = new LogConsole();
		} else {
			$logTypes = [];
			$dbResult = $db->select('log_type', [], ['log_type_id']);
			foreach ($dbResult->records() as $dbRecord) {
				$logTypes[] = $dbRecord->getInt('log_type_id');
			}
			$container = new LogConsoleDetail($accountIDs, $logTypes);
		}
		$container->go();
	}

}
