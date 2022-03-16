<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$account = $session->getAccount();

$variable = $session->getRequestVar('variable');
$type = $session->getRequestVar('type');

$db = Smr\Database::getInstance();

$container = Page::create('skeleton.php', 'admin/ip_view.php');
$template->assign('BackHREF', $container->href());

$container = Page::create('admin/account_close.php');
$template->assign('CloseHREF', $container->href());

$template->assign('type', $type);

if ($type == 'comp_share') {
	//another script for comp share
	require('comp_share.php');
	return;

} elseif ($type == 'list') {
	//=========================================================
	// List all IPs
	//=========================================================

	//we are listing ALL IPs
	$dbResult = $db->read('SELECT * FROM account_has_ip GROUP BY ip, account_id ORDER BY ip');
	$ip_array = [];
	//make sure we have enough but not too mant to reduce lag
	foreach ($dbResult->records() as $dbRecord) {
		$id = $dbRecord->getInt('account_id');
		$ip = $dbRecord->getField('ip');
		$host = $dbRecord->getField('host');
		$ip_array[] = ['ip' => $ip, 'id' => $id, 'host' => $host];
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
			$dbResult2 = $db->read('SELECT * FROM account_exceptions WHERE account_id = ' . $db->escapeNumber($account_id));
			if ($dbResult2->hasRecord()) {
				$ex = $dbResult2->record()->getField('reason');
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
	$accountID = (int)$variable;
	$template->assign('BanAccountID', $accountID);
	$summary = 'Account ' . $accountID . ' has had the following IPs at the following times.';
	$template->assign('Summary', $summary);
	$dbResult = $db->read('SELECT * FROM account_exceptions WHERE account_id = ' . $db->escapeNumber($variable));
	if ($dbResult->hasRecord()) {
		$ex = $dbResult->record()->getField('reason');
		$template->assign('Exception', $ex);
	}
	$viewAccount = SmrAccount::getAccount($accountID);
	$disabled = $viewAccount->isDisabled();
	if ($disabled !== false) {
		$template->assign('CloseReason', $disabled['Reason']);
	}
	$rows = [];
	$dbResult = $db->read('SELECT * FROM account_has_ip WHERE account_id = ' . $db->escapeNumber($variable) . ' ORDER BY time');
	foreach ($dbResult->records() as $dbRecord) {
		$rows[] = [
			'ip' => $dbRecord->getField('ip'),
			'date' => date($account->getDateTimeFormat(), $dbRecord->getInt('time')),
			'host' => $dbRecord->getField('host'),
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
		$dbResult = $db->read('SELECT * FROM account_has_ip WHERE ip = ' . $db->escapeString($ip) . ' ORDER BY account_id');

	} elseif ($type == 'alliance_ips') {
		//=========================================================
		// List all IPs for a specific alliance
		//=========================================================
		[$allianceID, $gameID] = preg_split('/[\/]/', $variable);
		if (!is_numeric($gameID) || !is_numeric($allianceID)) {
			create_error('Incorrect format used.');
		}
		$allianceID = (int)$allianceID;
		$gameID = (int)$gameID;
		$name = SmrAlliance::getAlliance($allianceID, $gameID)->getAllianceDisplayName();
		$dbResult = $db->read('SELECT ip.* FROM account_has_ip ip JOIN player USING(account_id) WHERE game_id = ' . $db->escapeNumber($gameID) . ' AND alliance_id = ' . $db->escapeNumber($allianceID) . ' ORDER BY ip');
		$summary = 'Listing all IPs for alliance ' . $name . ' in game with ID ' . $gameID;

	} elseif ($type == 'wild_log') {
		//=========================================================
		// List all IPs for a wildcard login name
		//=========================================================
		$dbResult = $db->read('SELECT ip.* FROM account_has_ip ip JOIN account USING(account_id) WHERE login LIKE ' . $db->escapeString($variable) . ' ORDER BY login, ip');
		$summary = 'Listing all IPs for login names LIKE ' . $variable;

	} elseif ($type == 'wild_in') {
		//=========================================================
		// List all IPs for a wildcard ingame name
		//=========================================================
		$dbResult = $db->read('SELECT ip.* FROM account_has_ip ip JOIN player USING(account_id) WHERE player_name LIKE ' . $db->escapeString($variable) . ' ORDER BY player_name, ip');
		$summary = 'Listing all IPs for ingame names LIKE ' . $variable;

	} elseif ($type == 'compare') {
		//=========================================================
		// List all IPs for specified players
		//=========================================================
		$list = preg_split('/[,]+[\s]/', $variable);
		$dbResult = $db->read('SELECT ip.* FROM account_has_ip ip JOIN player USING(account_id) WHERE player_name IN (' . $db->escapeArray($list) . ') ORDER BY ip');
		$summary = 'Listing all IPs for ingame names ' . $variable;

	} elseif ($type == 'compare_log') {
		//=========================================================
		// List all IPs for specified logins
		//=========================================================
		$list = preg_split('/[,]+[\s]/', $variable);
		$dbResult = $db->read('SELECT ip.* FROM account_has_ip ip JOIN account USING(account_id) WHERE login IN (' . $db->escapeArray($list) . ') ORDER BY ip');
		$summary = 'Listing all IPs for logins ' . $variable;

	} elseif ($type == 'wild_ip') {
		//=========================================================
		// Wildcard IP search
		//=========================================================
		$dbResult = $db->read('SELECT * FROM account_has_ip WHERE ip LIKE ' . $db->escapeString($variable) . ' GROUP BY account_id, ip ORDER BY time DESC, ip');
		$summary = 'Listing all IPs LIKE ' . $variable;

	} elseif ($type == 'wild_host') {
		//=========================================================
		// Wildcard host search
		//=========================================================
		$dbResult = $db->read('SELECT * FROM account_has_ip WHERE host LIKE ' . $db->escapeString($variable) . ' GROUP BY account_id, ip ORDER BY time, ip');
		$summary = 'Listing all hosts LIKE ' . $variable;
	} else {
		throw new Exception('Unknown type: ' . $type);
	}
	$template->assign('Summary', $summary);

	// initialize history variables
	$last_id = null;
	$last_ip = null;

	$rows = [];
	foreach ($dbResult->records() as $dbRecord) {
		$id = $dbRecord->getInt('account_id');
		$time = $dbRecord->getInt('time');
		$ip = $dbRecord->getField('ip');
		$host = $dbRecord->getField('host');

		if ($id === $last_id && $ip === $last_ip) {
			continue;
		}
		$acc = SmrAccount::getAccount($id);
		$disabled = $acc->isDisabled();
		$close_reason = $disabled ? $disabled['Reason'] : '';
		$dbResult2 = $db->read('SELECT * FROM player WHERE account_id = ' . $db->escapeNumber($id));
		$names = [];
		foreach ($dbResult2->records() as $dbRecord2) {
			$names[] = $dbRecord2->getString('player_name');
		}
		$dbResult2 = $db->read('SELECT * FROM account_exceptions WHERE account_id = ' . $db->escapeNumber($id));
		if ($dbResult2->hasRecord()) {
			$ex = $dbResult2->record()->getField('reason');
		} else {
			$ex = '';
		}
		$last_ip = $ip;
		$last_id = $id;

		$rows[] = [
			'account_id' => $id,
			'login' => $acc->getLogin(),
			'date' => date($account->getDateTimeFormat(), $time),
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
