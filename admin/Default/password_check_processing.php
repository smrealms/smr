<?php
$disable_account = $_REQUEST['disable_account'];
foreach ($disable_account as $currAccountID) {
	//never expire
	SmrAccount::getAccount($currAccountID)->banAccount(0,$account,2,'Double password');
}
forward(create_container('skeleton.php', 'admin_tools.php'));
?>