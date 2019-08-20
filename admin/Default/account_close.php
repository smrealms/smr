<?php declare(strict_types=1);

// Number of banned accounts
$amount = 0;

// Disabling from the "Computer Sharing" page
if (isset($_REQUEST['close'])) {
	//never expire
	$expire_time = 0;
	foreach ($_REQUEST['close'] as $key => $value) {
		$val = 'Match list:' . $value;
		$bannedAccount = SmrAccount::getAccount($key);
		$bannedAccount->banAccount($expire_time, $account, 2, $val);
		$amount++;
	}
}

if (isset($_REQUEST['first'])) {
	$same_ip = $_REQUEST['same_ip'];
	$val = 'Match list:' . implode(',', $same_ip);
	foreach ($same_ip as $account_id) {
		//never expire
		$bannedAccount = SmrAccount::getAccount($account_id);
		$bannedAccount->banAccount(0, $account, 2, $val);
		$amount++;
	}
}

// Disabling from the "List all IPs for a specific account" page
if (isset($_REQUEST['second'])) {
	//never expire
	$bannedAccount = SmrAccount::getAccount($_REQUEST['second']);
	$bannedAccount->banAccount(0, $account, 2, $_REQUEST['reason']);
	$amount++;
}

// Disabling from the "List all IPs" page
if (isset($_REQUEST['disable_id'])) {
	foreach ($_REQUEST['disable_id'] as $id) {

		$reason = $_REQUEST['suspicion'][$id];
		if (empty($reason)) {
			$reason = $_REQUEST['suspicion2'][$id];
		}

		//never expire
		$bannedAccount = SmrAccount::getAccount($id);
		$bannedAccount->banAccount(0, $account, 2, $reason);
		$amount++;
	}
}

$msg = 'You have disabled ' . $amount . ' accounts.';
if ($amount > 20) {
	$msg .= '  How do you sleep at night ;)';
}
$container = create_container('skeleton.php', 'admin_tools.php');
$container['msg'] = $msg;
forward($container);
