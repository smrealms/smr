<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use DateTime;
use Exception;
use Smr\Account;
use Smr\Database;
use Smr\Page\AccountPage;
use Smr\Template;

class LogConsoleDetail extends AccountPage {

	public string $file = 'admin/log_console_detail.php';

	/**
	 * @param array<int> $accountIDs
	 * @param array<int> $logTypeIDs
	 */
	public function __construct(
		private readonly array $accountIDs,
		private readonly array $logTypeIDs
	) {}

	public function build(Account $account, Template $template): void {
		$template->assign('PageTopic', 'Log Console - Detail');

		// get the account_ids from last form
		$account_ids = $this->accountIDs;

		// get the log_type_ids for log types to be displayed
		$log_type_ids = $this->logTypeIDs;

		$db = Database::getInstance();
		$account_list = $db->escapeArray($account_ids);

		// *********************************
		// * C o l o r   L e g e n d
		// *********************************
		$avail_colors = ['#FFFFFF', '#00FF00', '#FF3377', '#0099FF', '#FF0000', '#0000FF'];

		// now assign each account id a color
		$colors = [];
		foreach ($account_ids as $i => $id) {
			// assign it a color
			$color = $avail_colors[$i % count($avail_colors)];

			$dbResult = $db->read('SELECT login FROM account WHERE account_id = :account_id', [
				'account_id' => $db->escapeNumber($id),
			]);
			if ($dbResult->hasRecord()) {
				$colors[$id] = [
					'name' => $dbResult->record()->getString('login'),
					'color' => $color,
				];
			}
		}
		$template->assign('Colors', $colors);

		// *********************************
		// * L o g   T y p e s
		// *********************************
		$container = new LogConsoleDetailProcessor($account_ids);
		$template->assign('UpdateHREF', $container->href());

		$logTypes = [];
		$dbResult = $db->read('SELECT * FROM log_type');
		foreach ($dbResult->records() as $dbRecord) {
			$logTypes[$dbRecord->getInt('log_type_id')] = $dbRecord->getString('log_type_entry');
		}
		$template->assign('LogTypes', $logTypes);

		$log_type_id_list = [0];
		foreach ($logTypes as $id => $entry) {
			if (isset($log_type_ids[$id])) {
				$log_type_id_list[] = $id;
			}
		}
		$template->assign('LogTypesChecked', $log_type_id_list);

		// *********************************
		// * N o t e s
		// *********************************
		$container = new LogConsoleNotesProcessor($account_ids, $log_type_ids);
		$template->assign('SaveHREF', $container->href());

		// get notes from db
		$log_notes = [];
		$dbResult = $db->read('SELECT * FROM log_has_notes WHERE account_id IN (:account_ids)', [
			'account_ids' => $account_list,
		]);
		foreach ($dbResult->records() as $dbRecord) {
			$log_notes[] = $dbRecord->getString('notes');
		}

		// get rid of double values
		$log_notes = array_unique($log_notes);

		// flattens array
		$flat_notes = implode(EOL, $log_notes);
		$template->assign('FlatNotes', $flat_notes);

		// *********************************
		// * L o g   T a b l e
		// *********************************
		$logs = [];
		$dbResult = $db->read('SELECT * FROM account_has_logs WHERE account_id IN (:account_ids) AND log_type_id IN (:log_type_ids) ORDER BY microtime DESC', [
			'account_ids' => $account_list,
			'log_type_ids' => $db->escapeArray($log_type_id_list),
		]);
		foreach ($dbResult->records() as $dbRecord) {
			$account_id = $dbRecord->getInt('account_id');
			$microtime = $dbRecord->getFloat('microtime');
			$message = $dbRecord->getString('message');
			$log_type_id = $dbRecord->getInt('log_type_id');
			$sector_id = $dbRecord->getInt('sector_id');

			// DateTime only takes strings, and we need an explicit precision
			$millitime = sprintf('%.3f', $microtime);
			$datetime = DateTime::createFromFormat('U.v', $millitime);
			if ($datetime === false) {
				throw new Exception('Failed to parse time: ' . $millitime);
			}

			$logs[] = [
				'date' => $datetime->format('Y-m-d H:i:s.v'),
				'type' => $logTypes[$log_type_id],
				'sectorID' => $sector_id,
				'message' => $message,
				'color' => $colors[$account_id]['color'],
			];
		}
		$template->assign('Logs', $logs);

		$container = new LogConsole($account_ids);
		$template->assign('BackHREF', $container->href());
	}

}
