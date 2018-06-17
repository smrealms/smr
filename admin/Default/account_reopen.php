<?php
$account_id = $_REQUEST['account_id'];
$exception = $_REQUEST['exception'];
if (!is_array($account_id)) {
	create_error('Please check the boxes next to the names you wish to open.');
}

$action = $_REQUEST['action'];
if ($action == 'Reopen and add to exceptions') {
	foreach ($account_id as $id) {
		$curr_exception = $exception[$id];
		$bannedAccount = SmrAccount::getAccount($id);
		$bannedAccount->unbanAccount($account,$curr_exception);
	}
}
else {
	foreach ($account_id as $id) {
		$bannedAccount = SmrAccount::getAccount($id);
		$bannedAccount->unbanAccount($account);
	}
}

forward(create_container('skeleton.php', 'admin_tools.php'));
