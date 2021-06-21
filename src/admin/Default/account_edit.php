<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$db = Smr\Database::getInstance();
$var = Smr\Session::getInstance()->getCurrentVar();

$template->assign('PageTopic', 'Edit Account');

$account_id = $var['account_id'];
$curr_account = SmrAccount::getAccount($account_id);

$template->assign('EditingAccount', $curr_account);
$template->assign('EditFormHREF', Page::create('account_edit_processing.php', '', array('account_id' => $curr_account->getAccountID()))->href());
$template->assign('ResetFormHREF', Page::create('skeleton.php', 'account_edit_search.php')->href());

$editingPlayers = array();
$dbResult = $db->read('SELECT * FROM player WHERE account_id = ' . $db->escapeNumber($curr_account->getAccountID()) . ' ORDER BY game_id ASC');
foreach ($dbResult->records() as $dbRecord) {
	$editingPlayers[] = SmrPlayer::getPlayer($curr_account->getAccountID(), $dbRecord->getInt('game_id'), false, $dbRecord);
}
$template->assign('EditingPlayers', $editingPlayers);

$template->assign('Disabled', $curr_account->isDisabled());

$banReasons = array();
$dbResult = $db->read('SELECT * FROM closing_reason');
foreach ($dbResult->records() as $dbRecord) {
	$reason = $dbRecord->getField('reason');
	if (strlen($reason) > 61) {
		$reason = substr($reason, 0, 61) . '...';
	}
	$banReasons[$dbRecord->getInt('reason_id')] = $reason;
}
$template->assign('BanReasons', $banReasons);

$closingHistory = array();
$dbResult = $db->read('SELECT * FROM account_has_closing_history WHERE account_id = ' . $db->escapeNumber($curr_account->getAccountID()) . ' ORDER BY time DESC');
foreach ($dbResult->records() as $dbRecord) {
	// if an admin did it we get his/her name
	$admin_id = $dbRecord->getInt('admin_id');
	if ($admin_id > 0) {
		$admin = SmrAccount::getAccount($admin_id)->getLogin();
	} else {
		$admin = 'System';
	}
	$closingHistory[] = array(
		'Time' => $dbRecord->getInt('time'),
		'Action' => $dbRecord->getField('action'),
		'AdminName' => $admin
	);
}
$template->assign('ClosingHistory', $closingHistory);

$dbResult = $db->read('SELECT * FROM account_exceptions WHERE account_id = ' . $curr_account->getAccountID());
if ($dbResult->hasRecord()) {
	$template->assign('Exception', $dbResult->record()->getField('reason'));
}

$recentIPs = array();
$dbResult = $db->read('SELECT ip, time, host FROM account_has_ip WHERE account_id = ' . $db->escapeNumber($curr_account->getAccountID()) . ' ORDER BY time DESC');
foreach ($dbResult->records() as $dbRecord) {
	$recentIPs[] = array(
		'IP' => $dbRecord->getField('ip'),
		'Time' => $dbRecord->getField('time'),
		'Host' => $dbRecord->getField('host')
	);
}
$template->assign('RecentIPs', $recentIPs);
