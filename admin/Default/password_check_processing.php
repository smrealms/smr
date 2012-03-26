<?php
$disable_account = $_REQUEST['disable_account'];
foreach ($disable_account as $curr_account_id) {
	//never expire
	$bannedAccount =& SmrAccount::getAccount($curr_account_id);
	$bannedAccount->banAccount(0,$account,2,'Double password');
}
forward(create_container('skeleton.php', 'game_play.php'))
?>