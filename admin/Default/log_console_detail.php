<?php declare(strict_types=1);

$template->assign('PageTopic', 'Log Console - Detail');

// get the account_ids from last form
$account_ids = SmrSession::getRequestVar('account_ids');

// get the account_ids from last form
$log_type_ids = SmrSession::getRequestVar('log_type_ids');

// nothing marked?
if (!is_array($account_ids) || count($account_ids) == 0) {
	create_error('You have to select the log files you want to view/delete!');
}
$account_list = $db->escapeArray($account_ids);

$action = SmrSession::getRequestVar('action');
$template->assign('Action', $action);
if ($action == 'Delete') {

	// get rid of all entries
	$db->query('DELETE FROM account_has_logs WHERE account_id IN (' . $account_list . ')');
	$db->query('DELETE FROM log_has_notes WHERE account_id IN (' . $account_list . ')');

} else {
	// *********************************
	// * C o l o r   L e g e n d
	// *********************************
	$avail_colors = array('#FFFFFF', '#00FF00', '#FF3377', '#0099FF',
	                      '#FF0000', '#0000FF');

	// now assign each account id a color
	$colors = [];
	foreach ($account_ids as $i => $id) {
		// assign it a color
		$color = $avail_colors[$i % count($avail_colors)];

		$db->query('SELECT login FROM account WHERE account_id = ' . $db->escapeNumber($id));
		if ($db->nextRecord()) {
			$colors[$id] = [
				'name' => $db->getField('login'),
				'color' => $color,
			];
		}
	}
	$template->assign('Colors', $colors);

	// *********************************
	// * L o g   T y p e s
	// *********************************
	$container = create_container('skeleton.php', 'log_console_detail.php');
	$container['account_ids'] = $account_ids;
	$template->assign('UpdateHREF', SmrSession::getNewHREF($container));

	$logTypes = [];
	$db->query('SELECT * FROM log_type');
	while ($db->nextRecord()) {
		$logTypes[$db->getInt('log_type_id')] = $db->getField('log_type_entry');
	}
	$template->assign('LogTypes', $logTypes);

	$log_type_id_list = array(0);
	foreach ($logTypes as $id => $entry) {
		if (isset($log_type_ids[$id])) {
			$log_type_id_list[] = $id;
		}
	}
	$template->assign('LogTypesChecked', $log_type_id_list);

	// *********************************
	// * N o t e s
	// *********************************
	$container = create_container('log_notes_processing.php', '');
	$container['account_ids'] = $account_ids;
	$container['log_type_ids'] = $log_type_ids;
	$template->assign('SaveHREF', SmrSession::getNewHREF($container));

	// get notes from db
	$log_notes = array();
	$db->query('SELECT * FROM log_has_notes WHERE account_id IN (' . $account_list . ')');
	while ($db->nextRecord()) {
		$log_notes[] = $db->getField('notes');
	}

	// get rid of double values
	$log_notes = array_unique($log_notes);

	// flattens array
	$flat_notes = join(EOL, $log_notes);
	$template->assign('FlatNotes', $flat_notes);

	// *********************************
	// * L o g   T a b l e
	// *********************************
	$logs = [];
	$db->query('SELECT * FROM account_has_logs WHERE account_id IN (' . $account_list . ') AND log_type_id IN (' . $db->escapeArray($log_type_id_list) . ') ORDER BY microtime DESC');
	while ($db->nextRecord()) {
		$account_id = $db->getInt('account_id');
		$microtime = $db->getMicrotime('microtime', true); //fix value length errors
		$message = stripslashes($db->getField('message'));
		$log_type_id = $db->getInt('log_type_id');
		$sector_id = $db->getInt('sector_id');

		$date = DateTime::createFromFormat("U.u", $microtime)->format('Y-m-d H:i:s.u');

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

$container = create_container('skeleton.php', 'log_console.php');
$container['account_ids'] = $account_ids;
$template->assign('BackHREF', SmrSession::getNewHREF($container));
