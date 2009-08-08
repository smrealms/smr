<?php
$disable_account = $_REQUEST['disable_account'];
foreach ($disable_account as $curr_account_id)
{
	// check if this one already has an entry
	$db->query('SELECT * FROM account_is_closed WHERE account_id = '.$curr_account_id);
	if ($db->getNumRows() == 0)
	{
		//never expire
		$bannedAccount =& SmrAccount::getAccount($curr_account_id);
		$bannedAccount->banAccount(0,$account,2,'Double password');
	}
}
forward(create_container('skeleton.php', 'game_play.php'))
?>