<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Database;
use Smr\Page\AccountPage;
use Smr\Template;

class LogConsoleAnonBank extends AccountPage {

	public string $file = 'admin/log_anonymous_account.php';

	public function build(Account $account, Template $template): void {
		$template->assign('PageTopic', 'Anonymous Account Access');

		$db = Database::getInstance();
		$dbResult = $db->read('SELECT account_id FROM account_has_logs GROUP BY account_id');
		$log_account_ids = [];
		foreach ($dbResult->records() as $dbRecord) {
			$log_account_ids[] = $dbRecord->getInt('account_id');
		}

		// get all anon bank transactions that are logged in an array
		$dbResult = $db->read('SELECT * FROM anon_bank_transactions
		            JOIN account USING(account_id)
		            WHERE account_id IN (:account_ids)
		            ORDER BY game_id DESC, anon_id ASC', [
			'account_ids' => $db->escapeArray($log_account_ids),
		]);
		$anon_logs = [];
		foreach ($dbResult->records() as $dbRecord) {
			$transaction = strtolower($dbRecord->getString('transaction'));
			$anon_logs[$dbRecord->getInt('game_id')][$dbRecord->getInt('anon_id')][] = [
				'login' => $dbRecord->getString('login'),
				'amount' => number_format($dbRecord->getInt('amount')),
				'date' => date($account->getDateTimeFormat(), $dbRecord->getInt('time')),
				'type' => $transaction,
				'color' => $transaction === 'payment' ? 'tomato' : 'green',
			];
		}
		$template->assign('AnonLogs', $anon_logs);

		$container = new LogConsole();
		$template->assign('BackHREF', $container->href());
	}

}
