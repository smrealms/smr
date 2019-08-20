<?php declare(strict_types=1);
$variable = SmrSession::getRequestVar('variable');
$type = SmrSession::getRequestVar('type');

$db2 = new SmrMySqlDatabase();

$container = create_container('skeleton.php', 'ip_view.php');
$template->assign('BackHREF', SmrSession::getNewHREF($container));

$container = create_container('account_close.php');
$template->assign('CloseHREF', SmrSession::getNewHREF($container));

$template->assign('type', $type);

if ($type == 'comp_share') {
	//another script for comp share
	require(get_file_loc('comp_share.php'));
	return;

} elseif ($type == 'list') {
	//=========================================================
	// List all IPs
	//=========================================================

	//we are listing ALL IPs
	$db->query('SELECT * FROM account_has_ip GROUP BY ip, account_id ORDER BY ip');
	$ip_array = array();
	//make sure we have enough but not too mant to reduce lag
	while ($db->nextRecord()) {
		$id = $db->getField('account_id');
		$ip = $db->getField('ip');
		$host = $db->getField('host');
		$ip_array[] = array('ip' => $ip, 'id' => $id, 'host' => $host);
	}

	$rows = [];
	foreach ($ip_array as $db_ent) {
		$db_ip = $db_ent['ip'];
		$host = $db_ent['host'];
		$account_id = $db_ent['id'];
		$acc = SmrAccount::getAccount($account_id);
		$disabled = $acc->isDisabled();
		$close_reason = $disabled ? $disabled['Reason'] : '';

		$row = [
			'account_id' => $account_id,
			'login' => $acc->getLogin(),
			'ip' => $db_ip,
			'host' => $host,
			'close_reason' => $close_reason,
		];

		$match_id = 0;
		foreach ($ip_array as $db_ent2) {
			if ($db_ip == $db_ent2['ip'] && $account_id != $db_ent2['id']) {
				$match_id = $db_ent2['id'];
				break;
			}
		}
		$matches = $match_id > 0;
		$row['matches'] = $matches;

		if ($matches) {
			$db2->query('SELECT * FROM account_exceptions WHERE account_id = ' . $db2->escapeNumber($account_id));
			if ($db2->nextRecord()) {
				$ex = $db2->getField('reason');
			} else {
				$ex = '';
			}

			if (empty($ex) && empty($close_reason)) {
				$checked = 'checked';
			} else {
				$checked = '';
			}

			if (!empty($ex)) {
				$suspicion = 'DB Exception - ' . $ex;
			} else {
				$suspicion = 'Match:' . $match_id;
			}

			$row['checked'] = $checked;
			$row['suspicion'] = $suspicion;
		}
		$rows[] = $row;
	}
	$template->assign('Rows', $rows);

} elseif ($type == 'account_ips') {
	//=========================================================
	// List all IPs for a specific account (id)
	//=========================================================
	if (!is_numeric($variable)) {
		create_error('Account id must be numeric.');
	}
	$template->assign('BanAccountID', $variable);
	$summary = 'Account ' . $variable . ' has had the following IPs at the following times.';
	$template->assign('Summary', $summary);
	$db2->query('SELECT * FROM account_exceptions WHERE account_id = ' . $db->escapeNumber($variable));
	if ($db2->nextRecord()) {
		$ex = $db2->getField('reason');
		$template->assign('Exception', $ex);
	}
	$db2->query('SELECT * FROM account_is_closed JOIN closing_reason USING(reason_id) WHERE account_id = ' . $db->escapeNumber($variable));
	if ($db2->nextRecord()) {
		$close_reason = $db2->getField('reason');
		$template->assign('CloseReason', $close_reason);
	}
	$rows = [];
	$db->query('SELECT * FROM account_has_ip WHERE account_id = ' . $db->escapeNumber($variable) . ' ORDER BY time');
	while ($db->nextRecord()) {
		$rows[] = [
			'ip' => $db->getField('ip'),
			'date' => date(DATE_FULL_SHORT, $db->getField('time')),
			'host' => $db->getField('host'),
		];
	}
	$template->assign('Rows', $rows);


} elseif (in_array($type, ['search', 'alliance_ips', 'wild_log', 'wild_in', 'compare', 'compare_log', 'wild_ip', 'wild_host'])) {
	if ($type == 'search') {
		//=========================================================
		// Search for a specific IP
		//=========================================================
		$ip = $variable;
		$host = gethostbyaddr($ip);
		if ($host == $ip) {
			$host = 'unknown';
		}
		$summary = 'The following accounts have the IP address ' . $ip . ' (' . $host . ')';
		$db->query('SELECT * FROM account_has_ip WHERE ip = ' . $db->escapeString($ip) . ' ORDER BY account_id');

	} elseif ($type == 'alliance_ips') {
		//=========================================================
		// List all IPs for a specific alliance
		//=========================================================
		list ($alliance, $game) = preg_split('/[\/]/', $variable);
		if (!is_numeric($game) || !is_numeric($alliance)) {
			create_error('Incorrect format used.');
		}
		$name = SmrAlliance::getAlliance($alliance, $game)->getAllianceName();
		$db->query('SELECT ip.* FROM account_has_ip ip JOIN player USING(account_id) WHERE game_id = ' . $db->escapeNumber($game) . ' AND alliance_id = ' . $db->escapeNumber($alliance) . ' ORDER BY ip');
		$summary = 'Listing all IPs for alliance ' . $name . ' in game with ID ' . $game;

	} elseif ($type == 'wild_log') {
		//=========================================================
		// List all IPs for a wildcard login name
		//=========================================================
		$db->query('SELECT ip.* FROM account_has_ip ip JOIN account USING(account_id) WHERE login LIKE ' . $db->escapeString($variable) . ' ORDER BY login, ip');
		$summary = 'Listing all IPs for login names LIKE ' . $variable;

	} elseif ($type == 'wild_in') {
		//=========================================================
		// List all IPs for a wildcard ingame name
		//=========================================================
		$db->query('SELECT ip.* FROM account_has_ip ip JOIN player USING(account_id) WHERE player_name LIKE ' . $db->escapeString($variable) . ' ORDER BY player_name, ip');
		$summary = 'Listing all IPs for ingame names LIKE ' . $variable;

	} elseif ($type == 'compare') {
		//=========================================================
		// List all IPs for specified players
		//=========================================================
		$list = preg_split('/[,]+[\s]/', $variable);
		$db->query('SELECT ip.* FROM account_has_ip ip JOIN player USING(account_id) WHERE player_name IN (' . $db->escapeArray($list) . ') ORDER BY ip');
		$summary = 'Listing all IPs for ingame names ' . $variable;

	} elseif ($type == 'compare_log') {
		//=========================================================
		// List all IPs for specified logins
		//=========================================================
		$list = preg_split('/[,]+[\s]/', $variable);
		$db->query('SELECT ip.* FROM account_has_ip ip JOIN account USING(account_id) WHERE login IN (' . $db->escapeArray($list) . ') ORDER BY ip');
		$summary = 'Listing all IPs for logins ' . $variable;

	} elseif ($type == 'wild_ip') {
		//=========================================================
		// Wildcard IP search
		//=========================================================
		$db->query('SELECT * FROM account_has_ip WHERE ip LIKE ' . $db->escapeString($variable) . ' GROUP BY account_id, ip ORDER BY time DESC, ip');
		$summary = 'Listing all IPs LIKE ' . $variable;

	} elseif ($type == 'wild_host') {
		//=========================================================
		// Wildcard host search
		//=========================================================
		$db->query('SELECT * FROM account_has_ip WHERE host LIKE ' . $db->escapeString($variable) . ' GROUP BY account_id, ip ORDER BY time, ip');
		$summary = 'Listing all hosts LIKE ' . $variable;
	}
	$template->assign('Summary', $summary);

	// initialize history variables
	$last_id = null;
	$last_ip = null;

	$rows = [];
	while ($db->nextRecord()) {
		$id = $db->getField('account_id');
		$time = $db->getField('time');
		$ip = $db->getField('ip');
		$host = $db->getField('host');

		if ($id == $last_id && $ip == $last_ip) {
			continue;
		}
		$acc = SmrAccount::getAccount($id);
		$disabled = $acc->isDisabled();
		$close_reason = $disabled ? $disabled['Reason'] : '';
		$db2->query('SELECT * FROM player WHERE account_id = ' . $db2->escapeNumber($id));
		$names = array();
		while ($db2->nextRecord()) {
			$names[] = stripslashes($db2->getField('player_name'));
		}
		$db2->query('SELECT * FROM account_exceptions WHERE account_id = ' . $db2->escapeNumber($id));
		if ($db2->nextRecord()) {
			$ex = $db2->getField('reason');
		} else {
			$ex = '';
		}
		$last_ip = $ip;
		$last_id = $id;

		$rows[] = [
			'account_id' => $id,
			'login' => $acc->getLogin(),
			'date' => date(DATE_FULL_SHORT, $time),
			'ip' => $ip,
			'host' => $host,
			'names' => implode(', ', array_unique($names)),
			'exception' => $ex,
			'close_reason' => $close_reason,
		];
	}
	$template->assign('Rows', $rows);

}

$template->assign('PageTopic', 'IP Search Results');
