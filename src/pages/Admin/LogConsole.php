<?php declare(strict_types=1);

use Smr\Database;

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();

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
		'Checked' => isset($var['account_ids']) && in_array($accountID, $var['account_ids']),
		'Notes' => '',
	];

	$dbResult2 = $db->read('SELECT notes FROM log_has_notes WHERE account_id = ' . $db->escapeNumber($accountID));
	if ($dbResult2->hasRecord()) {
		$loggedAccounts[$accountID]['Notes'] = nl2br($dbResult2->record()->getString('notes'));
	}
}
$template->assign('LoggedAccounts', $loggedAccounts);

if (count($loggedAccounts) > 0) {
	// put hidden fields in for log type to have all fields selected on next page.
	$logTypes = [];
	$dbResult = $db->read('SELECT log_type_id FROM log_type');
	foreach ($dbResult->records() as $dbRecord) {
		$logTypes[] = $dbRecord->getInt('log_type_id');
	}
	$template->assign('LogTypes', $logTypes);

	$template->assign('LogConsoleFormHREF', Page::create('admin/log_console_detail.php')->href());
	$template->assign('AnonAccessHREF', Page::create('admin/log_anonymous_account.php')->href());
}
