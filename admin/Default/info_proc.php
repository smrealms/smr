<?php

$action = $_REQUEST['action'];
if ($action == 'Reopen and Add Exception' || $action == 'Reopen without Exception') {
	$account_id = $_REQUEST['account_id'];
	$exception = $_REQUEST['exception'];
	if (!is_array($account_id))
		create_error('Please check the boxes next to the names you wish to open.');
	
	foreach ($account_id as $id) {
		$bannedAccount =& SmrAccount::getAccount($id);
		if ($action == 'Reopen and Add Exception') {
			$curr_exception = $exception[$id];
			$bannedAccount->unbanAccount($account,$curr_exception);
		}
		else
			$bannedAccount->unbanAccount($account);
	}
}
elseif ($action == 'Ban' || $action == 'Ban and remove exception') {
	$ban = $_REQUEST['ban'];
	$bancheck = $_REQUEST['bancheck'];
	foreach ($bancheck as $id) {
		//never expire
		$bannedAccount =& SmrAccount::getAccount($id);
		$bannedAccount->banAccount(0,$account,2,$ban[$id],$action == 'Ban and remove exception');
	}
}
forward(create_container('skeleton.php', 'admin_tools.php'));
?>