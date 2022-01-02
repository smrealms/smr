<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$account = $session->getAccount();

// Number of banned accounts
$amount = 0;

// Disabling from the "Computer Sharing" page
if (Smr\Request::has('close')) {
	//never expire
	$expire_time = 0;
	foreach (Smr\Request::getArray('close') as $key => $value) {
		$val = 'Match list:' . $value;
		$bannedAccount = SmrAccount::getAccount($key);
		$bannedAccount->banAccount($expire_time, $account, BAN_REASON_MULTI, $val);
		$amount++;
	}
}

if (Smr\Request::has('first')) {
	$same_ip = Smr\Request::getIntArray('same_ip');
	$val = 'Match list:' . implode(',', $same_ip);
	foreach ($same_ip as $account_id) {
		//never expire
		$bannedAccount = SmrAccount::getAccount($account_id);
		$bannedAccount->banAccount(0, $account, BAN_REASON_MULTI, $val);
		$amount++;
	}
}

// Disabling from the "List all IPs for a specific account" page
if (Smr\Request::has('second')) {
	//never expire
	$bannedAccount = SmrAccount::getAccount(Smr\Request::getInt('second'));
	$bannedAccount->banAccount(0, $account, BAN_REASON_MULTI, Smr\Request::get('reason'));
	$amount++;
}

// Disabling from the "List all IPs" page
if (Smr\Request::has('disable_id')) {
	$reasons = Smr\Request::getArray('suspicion');
	$reasons2 = Smr\Request::getArray('suspicion2');
	foreach (Smr\Request::getIntArray('disable_id') as $id) {

		$reason = $reasons[$id];
		if (empty($reason)) {
			$reason = $reasons2[$id];
		}

		//never expire
		$bannedAccount = SmrAccount::getAccount($id);
		$bannedAccount->banAccount(0, $account, BAN_REASON_MULTI, $reason);
		$amount++;
	}
}

$msg = 'You have disabled ' . $amount . ' accounts.';
if ($amount > 20) {
	$msg .= '  How do you sleep at night ;)';
}
$container = Page::create('skeleton.php', 'admin/admin_tools.php');
$container['msg'] = $msg;
$container->go();
