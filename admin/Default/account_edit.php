<?php

$template->assign('PageTopic','Edit Account');

$account_id = SmrSession::getRequestVar('account_id', '');
$player_name = SmrSession::getRequestVar('player_name', '');
SmrSession::getRequestVar('login', '');
SmrSession::getRequestVar('val_code', '');
SmrSession::getRequestVar('email', '');
SmrSession::getRequestVar('hofname', '');
if(isset($_REQUEST['game_id'])) {
	SmrSession::updateVar('SearchGameID',$_REQUEST['game_id']);
}
elseif(!isset($var['SearchGameID'])) {
	SmrSession::updateVar('SearchGameID', 0);
}

if (empty($account_id)) {
	$account_id = 0;
} elseif (!is_numeric($account_id)) {
	create_error('Account ID must be a number.');
}

// create account object
$curr_account = false;

if (!empty($player_name) && !is_array($player_name)) {
	$gameIDClause = $var['SearchGameID'] != 0 ? ' AND game_id = ' . $db->escapeNumber($var['SearchGameID']) . ' ': '';
	$db->query('SELECT account_id FROM player
				WHERE player_name = ' . $db->escapeString($player_name) . $gameIDClause . '
				ORDER BY game_id DESC LIMIT 1');
	if ($db->nextRecord()) {
		$account_id = $db->getInt('account_id');
	}
	else {
		$db->query('SELECT * FROM player
					WHERE player_name LIKE ' . $db->escapeString($player_name . '%') . $gameIDClause);
		if ($db->nextRecord()) {
			$account_id = $db->getInt('account_id');
		}
	}
}

// get account from db
$db->query('SELECT account_id FROM account WHERE account_id = '.$db->escapeNumber($account_id).' OR ' .
									   'login LIKE ' . $db->escape_string($var['login']) . ' OR ' .
									   'email LIKE ' . $db->escape_string($var['email']) . ' OR ' .
									   'hof_name LIKE ' . $db->escapeString($var['hofname']) . ' OR ' .
									   'validation_code LIKE ' . $db->escape_string($var['val_code']));
if ($db->nextRecord()) {
	$curr_account =& SmrAccount::getAccount($db->getField('account_id'));
	$template->assignByRef('EditingAccount', $curr_account);
	$template->assign('EditFormHREF', SmrSession::getNewHREF(create_container('account_edit_processing.php', '', array('account_id' => $curr_account->getAccountID()))));
}
else {
	$template->assign('EditFormHREF', SmrSession::getNewHREF(create_container('skeleton.php', 'account_edit.php')));
}
$template->assign('ResetFormHREF', SmrSession::getNewHREF(create_container('skeleton.php', 'account_edit.php')));


if ($curr_account===false) {
	$games = array();
	$db->query('SELECT game_id FROM game ORDER BY end_date DESC');
	while ($db->nextRecord()) {
		$games[] = SmrGame::getGame($db->getInt('game_id'));
	}
	$template->assignByRef('Games', $games);
}
else {
	$editingPlayers = array();
	$db->query('SELECT game_id FROM player WHERE account_id = ' . $db->escapeNumber($curr_account->getAccountID()) . ' ORDER BY game_id ASC');
	while ($db->nextRecord()) {
		$editingPlayers[] =& SmrPlayer::getPlayer($curr_account->getAccountID(), $db->getInt('game_id'));
	}
	$template->assign('EditingPlayers', $editingPlayers);

	$banReasons = array();
	$db->query('SELECT * FROM closing_reason');
	while ($db->nextRecord()) {
		$reason = $db->getField('reason');
		if (strlen($reason) > 50) {
			$reason = substr($reason, 0, 75) . '...';
		}
		$banReasons[$db->getInt('reason_id')] = $reason;
	}
	$template->assign('BanReasons', $banReasons);

	$closingHistory = array();
	$db->query('SELECT * FROM account_has_closing_history WHERE account_id = ' . $db->escapeNumber($curr_account->getAccountID()) . ' ORDER BY time DESC');
	while ($db->nextRecord()) {
		// if an admin did it we get his/her name
		if ($admin_id > 0) {
			$admin = SmrAccount::getAccount($db->getInt('admin_id'))->getLogin();
		}
		else {
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
	$template->assign('RecentIPs',$recentIPs);
}


if (isset($var['errorMsg'])) {
	$template->assign('ErrorMessage', $var['errorMsg']);
}
if (isset($var['msg'])) {
	$template->assign('Message', $var['msg']);
}

?>
