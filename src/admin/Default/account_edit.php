<?php declare(strict_types=1);

$template->assign('PageTopic', 'Edit Account');

$account_id = $var['account_id'];
$curr_account = SmrAccount::getAccount($account_id);

$template->assign('EditingAccount', $curr_account);
$template->assign('EditFormHREF', SmrSession::getNewHREF(create_container('account_edit_processing.php', '', array('account_id' => $curr_account->getAccountID()))));
$template->assign('ResetFormHREF', SmrSession::getNewHREF(create_container('skeleton.php', 'account_edit_search.php')));

$editingPlayers = array();
$db->query('SELECT * FROM player WHERE account_id = ' . $db->escapeNumber($curr_account->getAccountID()) . ' ORDER BY game_id ASC');
while ($db->nextRecord()) {
	$editingPlayers[] = SmrPlayer::getPlayer($curr_account->getAccountID(), $db->getInt('game_id'), false, $db);
}
$template->assign('EditingPlayers', $editingPlayers);

$template->assign('Disabled', $curr_account->isDisabled());

$banReasons = array();
$db->query('SELECT * FROM closing_reason');
while ($db->nextRecord()) {
	$reason = $db->getField('reason');
	if (strlen($reason) > 61) {
		$reason = substr($reason, 0, 61) . '...';
	}
	$banReasons[$db->getInt('reason_id')] = $reason;
}
$template->assign('BanReasons', $banReasons);

$closingHistory = array();
$db->query('SELECT * FROM account_has_closing_history WHERE account_id = ' . $db->escapeNumber($curr_account->getAccountID()) . ' ORDER BY time DESC');
while ($db->nextRecord()) {
	// if an admin did it we get his/her name
	$admin_id = $db->getInt('admin_id');
	if ($admin_id > 0) {
		$admin = SmrAccount::getAccount($admin_id)->getLogin();
	} else {
		$admin = 'System';
	}
	$closingHistory[] = array(
		'Time' => $db->getInt('time'),
		'Action' => $db->getField('action'),
		'AdminName' => $admin
	);
}
$template->assign('ClosingHistory', $closingHistory);

$db->query('SELECT * FROM account_exceptions WHERE account_id = ' . $curr_account->getAccountID());
if ($db->nextRecord()) {
	$template->assign('Exception', $db->getField('reason'));
}

$recentIPs = array();
$db->query('SELECT ip, time, host FROM account_has_ip WHERE account_id = ' . $db->escapeNumber($curr_account->getAccountID()) . ' ORDER BY time DESC');
while ($db->nextRecord()) {
	$recentIPs[] = array(
		'IP' => $db->getField('ip'),
		'Time' => $db->getField('time'),
		'Host' => $db->getField('host')
	);
}
$template->assign('RecentIPs', $recentIPs);
