<?php declare(strict_types=1);

use Smr\Database;

		$template = Smr\Template::getInstance();
		$session = Smr\Session::getInstance();

		$template->assign('PageTopic', 'Log Console - Detail');

		// get the account_ids from last form
		$account_ids = $session->getRequestVarIntArray('account_ids');

		// get the log_type_ids for log types to be displayed
		$log_type_ids = $session->getRequestVarIntArray('log_type_ids');

		// nothing marked?
		if (count($account_ids) == 0) {
			create_error('You have to select the log files you want to view/delete!');
		}
		$db = Database::getInstance();
		$account_list = $db->escapeArray($account_ids);

		$action = $session->getRequestVar('action');
		$template->assign('Action', $action);
		if ($action == 'Delete') {

			// get rid of all entries
			$db->write('DELETE FROM account_has_logs WHERE account_id IN (' . $account_list . ')');
			$db->write('DELETE FROM log_has_notes WHERE account_id IN (' . $account_list . ')');

		} else {
			// *********************************
			// * C o l o r   L e g e n d
			// *********************************
			$avail_colors = ['#FFFFFF', '#00FF00', '#FF3377', '#0099FF', '#FF0000', '#0000FF'];

			// now assign each account id a color
			$colors = [];
			foreach ($account_ids as $i => $id) {
				// assign it a color
				$color = $avail_colors[$i % count($avail_colors)];

				$dbResult = $db->read('SELECT login FROM account WHERE account_id = ' . $db->escapeNumber($id));
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
			$container = Page::create('admin/log_console_detail.php');
			$container['account_ids'] = $account_ids;
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
			$container = Page::create('admin/log_notes_processing.php');
			$container['account_ids'] = $account_ids;
			$container['log_type_ids'] = $log_type_ids;
			$template->assign('SaveHREF', $container->href());

			// get notes from db
			$log_notes = [];
			$dbResult = $db->read('SELECT * FROM log_has_notes WHERE account_id IN (' . $account_list . ')');
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
			$dbResult = $db->read('SELECT * FROM account_has_logs WHERE account_id IN (' . $account_list . ') AND log_type_id IN (' . $db->escapeArray($log_type_id_list) . ') ORDER BY microtime DESC');
			foreach ($dbResult->records() as $dbRecord) {
				$account_id = $dbRecord->getInt('account_id');
				$microtime = $dbRecord->getFloat('microtime');
				$message = $dbRecord->getString('message');
				$log_type_id = $dbRecord->getInt('log_type_id');
				$sector_id = $dbRecord->getInt('sector_id');

				// DateTime only takes strings, and we need an explicit precision
				$millitime = sprintf('%.3f', $microtime);
				$date = DateTime::createFromFormat('U.v', $millitime)->format('Y-m-d H:i:s.v');

				$logs[] = [
					'date' => $date,
					'type' => $logTypes[$log_type_id],
					'sectorID' => $sector_id,
					'message' => $message,
					'color' => $colors[$account_id]['color'],
				];
			}
			$template->assign('Logs', $logs);
		}

		$container = Page::create('admin/log_console.php');
		$container['account_ids'] = $account_ids;
		$template->assign('BackHREF', $container->href());
