<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$account = $session->getAccount();

$template->assign('PageTopic', 'Computer Sharing');

//future features
$skipUnusedAccs = true;
$skipClosedAccs = false;
$skipExceptions = false;

$used = [];

//check the db and get the info we need
$db = Smr\Database::getInstance();
$dbResult = $db->read('SELECT * FROM multi_checking_cookie WHERE `use` = \'TRUE\'');
$tables = [];
foreach ($dbResult->records() as $dbRecord) {
	//get info about linked IDs
	$accountIDs = explode('-', $dbRecord->getString('array'));

	//make sure this is good data.
	$cookieVersion = array_shift($accountIDs);
	if ($cookieVersion != MULTI_CHECKING_COOKIE_VERSION) {
		continue;
	}

	//how many are they linked to?
	$rows = count($accountIDs);

	$currTabAccId = $dbRecord->getInt('account_id');

	//if this account was listed with another we can skip it.
	if (isset($used[$currTabAccId])) {
		continue;
	}

	if ($rows > 1) {
		$dbResult2 = $db->read('SELECT login FROM account WHERE account_id =' . $db->escapeNumber($currTabAccId) . ($skipUnusedAccs ? ' AND last_login > ' . $db->escapeNumber(Smr\Epoch::time() - 86400 * 30) : '') . ' LIMIT 1');
		if (!$dbResult2->hasRecord()) {
			continue;
		}
		$currTabAccLogin = $dbResult2->record()->getField('login');

		if ($skipClosedAccs) {
			$dbResult2 = $db->read('SELECT 1 FROM account_is_closed WHERE account_id = ' . $db->escapeNumber($currTabAccId));
			if ($dbResult2->hasRecord()) {
				continue;
			}
		}

		if ($skipExceptions) {
			$dbResult2 = $db->read('SELECT 1 FROM account_exceptions WHERE account_id = ' . $db->escapeNumber($currTabAccId));
			if ($dbResult2->hasRecord()) {
				continue;
			}
		}

		$rows = [];
		foreach ($accountIDs as $currLinkAccId) {
			$dbResult2 = $db->read('SELECT account_id, login, email, validated, last_login, (SELECT ip FROM account_has_ip WHERE account_id = account.account_id GROUP BY ip ORDER BY COUNT(ip) DESC LIMIT 1) common_ip FROM account WHERE account_id = ' . $db->escapeNumber($currLinkAccId) . ($skipUnusedAccs ? ' AND last_login > ' . $db->escapeNumber(Smr\Epoch::time() - 86400 * 30) : ''));
			if (!$dbResult2->hasRecord()) {
				continue;
			}
			$dbRecord2 = $dbResult2->record();
			$currLinkAccLogin = $dbRecord2->getField('login');

			$style = $dbRecord2->getBoolean('validated') ? '' : 'text-decoration:line-through;';
			$email = $dbRecord2->getField('email');
			$valid = $dbRecord2->getBoolean('validated') ? 'Valid' : 'Invalid';
			$common_ip = $dbRecord2->getField('common_ip');
			$last_login = date($account->getDateTimeFormat(), $dbRecord2->getInt('last_login'));

			$dbResult2 = $db->read('SELECT * FROM account_is_closed WHERE account_id = ' . $db->escapeNumber($currLinkAccId));
			$isDisabled = $dbResult2->hasRecord();
			if ($isDisabled) {
				$suspicion = $dbResult2->record()->getField('suspicion');
			} else {
				$suspicion = '';
			}

			$dbResult2 = $db->read('SELECT * FROM account_exceptions WHERE account_id = ' . $db->escapeNumber($currLinkAccId));
			if ($dbResult2->hasRecord()) {
				$exception = $dbResult2->record()->getField('reason');
			} else {
				$exception = '';
			}

			$used[$currLinkAccId] = true;

			$rows[] = [
				'name' => $currLinkAccLogin . ' (' . $currLinkAccId . ')',
				'account_id' => $currLinkAccId,
				'associated_ids' => implode('-', $accountIDs),
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
