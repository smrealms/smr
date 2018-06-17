<?php
$close = $_REQUEST['close'];
if (isset($close)) {

	//get accs to close
	$reason = $_REQUEST['reason'];
	//never expire
	$expire_time = 0;
	$amount = 0;
	foreach ($close as $key => $value) {

		$val = 'Match list:';
		$val .= $value;
		$bannedAccount = SmrAccount::getAccount($key);
		$bannedAccount->banAccount($expire_time,$account,2,$val);
		$amount++;
	}

}
$first = $_REQUEST['first'];
if (isset($first)) {

	$same_ip = $_REQUEST['same_ip'];
	$val = 'Match list:' . implode(',', $same_ip);
	foreach ($same_ip as $account_id) {
		//never expire
		$bannedAccount = SmrAccount::getAccount($account_id);
		$bannedAccount->banAccount(0,$account,2,$val);
	}
	forward(create_container('skeleton.php', 'admin_tools.php'));

}
$second = $_REQUEST['second'];
if (isset($second)) {
	//never expire
	$bannedAccount = SmrAccount::getAccount($second);
	$bannedAccount->banAccount(0,$account,2,'Auto:By Admin');
}
$action = $_REQUEST['action'];
if($action == 'Next Page No Disable') {
	//we have to send back to ip check page now since we dont disable
	if (!isset($var['continue'])) {
		$container = create_container('skeleton.php', 'admin_tools.php');
	}
	else {
		$container = create_container('skeleton.php', 'ip_view_results.php');
	}
	$container['variable'] = $variable;
	$container['last_ip'] = $last_ip;
	$container['last_acc'] = $last_acc;
	$container['total'] = $total;
	$container['type'] = $type;
	$closed_so_far = $_REQUEST['closed_so_far'];
	if (isset($closed_so_far))
		$container['closed_so_far'] = $closed_so_far;
	forward($container);
}
$disable_id = $_REQUEST['disable_id'];
if (isset($disable_id)) {
	$suspicion = $_REQUEST['suspicion'];
	$suspicion2 = $_REQUEST['suspicion2'];
	foreach ($disable_id as $id) {

		// generate list of messages that should be deleted
		$reason = $suspicion[$id];
		if (empty($reason) || $reason == '') {
			$reason = $suspicion2[$id];
		}
		$db->query('SELECT * FROM account_is_closed WHERE account_id = '.$db->escapeNumber($id));
		if (!$db->getNumRows()) {
			$amount += 1;
		}

		//never expire
		$bannedAccount = SmrAccount::getAccount($id);
		$bannedAccount->banAccount(0,$account,2,$reason);
	}
}
if (isset($_REQUEST['amount'])) $amount = $_REQUEST['amount'];
if (!isset($amount))
	$amount = 0;
$closed_so_far = $_REQUEST['closed_so_far'];
if (isset($closed_so_far))
	$amount += $closed_so_far;
$msg = 'You have disabled '.$amount.' accounts.';
if ($amount > 20) {
	$msg .= '  How do you sleep at night ;)';
}
$container = create_container('skeleton.php');
$container['type'] = $type;
$container['last_ip'] = $last_ip;
$container['last_acc'] = $last_acc;
$container['total'] = $total;
if (isset($var['continue'])) {
	//we have to send back to ip check page
	$container['variable'] = $variable;
	$container['closed_so_far'] = $amount;
	$container['body'] = 'ip_view_results.php';
} else {
	$container['body'] = 'admin_tools.php';
	$container['msg'] = $msg;
}
forward($container);
