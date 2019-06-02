<?php

$template->assign('PageTopic', 'Computer Sharing');

//future features
$skipUnusedAccs = true;
$skipClosedAccs = false;
$skipExceptions = false;

//extra db object and other vars
$db2 = new SmrMySqlDatabase();
$used = array();

//check the db and get the info we need
$db->query('SELECT * FROM multi_checking_cookie WHERE `use` = \'TRUE\'');
$tables = [];
while ($db->nextRecord()) {
	//get info about linked IDs
	$accountIDs = explode('-', $db->getField('array'));

	//make sure this is good data.
	$cookieVersion = array_shift($accountIDs);
	if ($cookieVersion != MULTI_CHECKING_COOKIE_VERSION) {
		continue;
	}

	//how many are they linked to?
	$rows = sizeof($accountIDs);

	$currTabAccId = $db->getField('account_id');

	//if this account was listed with another we can skip it.
	if (isset($used[$currTabAccId])) {
		continue;
	}

	if ($rows > 1) {
		$db2->query('SELECT account_id, login FROM account WHERE account_id =' . $db2->escapeNumber($currTabAccId) . ($skipUnusedAccs ? ' AND last_login > ' . $db2->escapeNumber(TIME - 86400 * 30) : '') . ' LIMIT 1');
		if ($db2->nextRecord()) {
			$currTabAccLogin = $db2->getField('login');
		} else {
			continue;
		}

		if ($skipClosedAccs) {
			$db2->query('SELECT * FROM account_is_closed WHERE account_id = ' . $db2->escapeNumber($currTabAccId));
			if ($db2->nextRecord()) {
				continue;
			}
		}

		if ($skipExceptions) {
			$db2->query('SELECT * FROM account_exceptions WHERE account_id = ' . $db2->escapeNumber($currTabAccId));
			if ($db2->nextRecord()) {
				continue;
			}
		}

		$rows = [];
		foreach ($accountIDs as $currLinkAccId) {
			$db2->query('SELECT account_id, login, email, validated, last_login, (SELECT ip FROM account_has_ip WHERE account_id = account.account_id GROUP BY ip ORDER BY COUNT(ip) DESC LIMIT 1) common_ip FROM account WHERE account_id = ' . $db2->escapeNumber($currLinkAccId) . ($skipUnusedAccs ? ' AND last_login > ' . $db2->escapeNumber(TIME - 86400 * 30) : ''));
			if ($db2->nextRecord()) {
				$currLinkAccLogin = $db2->getField('login');
			} else {
				continue;
			}

			$style = $db2->getBoolean('validated') ? '' : ' style="text-decoration:line-through;"';
			$email = $db2->getField('email');
			$valid = $db2->getBoolean('validated') ? 'Valid' : 'Invalid';
			$common_ip = $db2->getField('common_ip');
			$last_login = date(DATE_FULL_SHORT, $db2->getField('last_login'));

			$db2->query('SELECT * FROM account_is_closed WHERE account_id = ' . $db2->escapeNumber($currLinkAccId));
			$isDisabled = $db2->nextRecord();
			$suspicion = $db2->getField('suspicion');

			$db2->query('SELECT * FROM account_exceptions WHERE account_id = ' . $db2->escapeNumber($currLinkAccId));
			$exception = $db2->nextRecord() ? $db2->getField('reason') : '';

			$used[$currLinkAccId] = TRUE;

			$rows[] = [
				'name' => $currLinkAccLogin . ' (' . $currLinkAccId . ')',
				'account_id' => $currLinkAccId,
				'associated_ids' => join('-', $accountIDs),
				'style' => $style,
				'color' => $isDisabled ? 'red' : '',
				'common_ip' => $common_ip,
				'last_login' => $last_login,
				'suspicion' => $suspicion,
				'exception' => $exception,
				'email' => $email . ' (' . $valid . ')',
			];
		}
		$tables[] = $rows;
	}
}
$template->assign('Tables', $tables);
