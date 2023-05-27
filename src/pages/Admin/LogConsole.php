<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Database;
use Smr\Page\AccountPage;
use Smr\Template;

class LogConsole extends AccountPage {

	public string $file = 'admin/log_console.php';

	/**
	 * @param array<int> $accountIDs
	 */
	public function __construct(
		private readonly array $accountIDs = [],
	) {}

	public function build(Account $account, Template $template): void {
		$template->assign('PageTopic', 'Log Console');

		$loggedAccounts = [];

		$db = Database::getInstance();
		$dbResult = $db->read('SELECT account_id as account_id, login, count(*) as number_of_entries
					FROM account_has_logs
					JOIN account USING(account_id)
					GROUP BY account_id');
		foreach ($dbResult->records() as $dbRecord) {
			$accountID = $dbRecord->getInt('account_id');
			$loggedAccounts[$accountID] = [
				'AccountID' => $accountID,
				'Login' => $dbRecord->getString('login'),
				'TotalEntries' => $dbRecord->getInt('number_of_entries'),
				'Checked' => in_array($accountID, $this->accountIDs, true),
				'Notes' => '',
			];

			$dbResult2 = $db->read('SELECT notes FROM log_has_notes WHERE account_id = :account_id', [
				'account_id' => $db->escapeNumber($accountID),
			]);
			if ($dbResult2->hasRecord()) {
				$loggedAccounts[$accountID]['Notes'] = nl2br($dbResult2->record()->getString('notes'));
			}
		}
		$template->assign('LoggedAccounts', $loggedAccounts);

		if (count($loggedAccounts) > 0) {
			$template->assign('LogConsoleFormHREF', (new LogConsoleProcessor())->href());
			$template->assign('AnonAccessHREF', (new LogConsoleAnonBank())->href());
		}
	}

}
