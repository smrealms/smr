<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Database;
use Smr\Page\AccountPageProcessor;
use Smr\Request;

class LogConsoleProcessor extends AccountPageProcessor {

	public function build(Account $account): never {
		$accountIDs = Request::getIntArray('account_ids');
		// nothing marked?
		if (count($accountIDs) == 0) {
			create_error('You have to select the log files you want to view/delete!');
		}

		$db = Database::getInstance();
		$action = Request::get('action');
		if ($action == 'Delete') {
			// get rid of all entries
			$db->write('DELETE FROM account_has_logs WHERE account_id IN (' . $db->escapeArray($accountIDs) . ')');
			$db->write('DELETE FROM log_has_notes WHERE account_id IN (' . $db->escapeArray($accountIDs) . ')');
			$container = new LogConsole();
		} else {
			$logTypes = [];
			$dbResult = $db->read('SELECT log_type_id FROM log_type');
			foreach ($dbResult->records() as $dbRecord) {
				$logTypes[] = $dbRecord->getInt('log_type_id');
			}
			$container = new LogConsoleDetail($accountIDs, $logTypes);
		}
		$container->go();
	}

}
