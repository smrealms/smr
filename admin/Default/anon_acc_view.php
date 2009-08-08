<?php

//view anon acct activity.
$template->assign('PageTopic','VIEW ANON ACCOUNT INFO');
//do we have an acct?
if (empty($_REQUEST['anon_account'])||empty($_REQUEST['game_id'])) {

	$container = array();
	$container['url'] = 'skeleton.php';
	$container['body'] = 'anon_acc_view.php';
	$PHP_OUTPUT.=('What account would you like to view?<br />');
	$PHP_OUTPUT.=create_echo_form($container);
	$PHP_OUTPUT.=('Account ID: <input type="text" name="anon_account" /><br />');
	$PHP_OUTPUT.=('Game ID: <input type="text" name="game_id" /><br />');
	$PHP_OUTPUT.=create_submit('Continue');
	$PHP_OUTPUT.=('</form>');
	
}
else
{
	//db object
	$db2 = new SmrMySqlDatabase();
	//split the name
	$acc = $_REQUEST['anon_account'];
	$game = $_REQUEST['game_id'];
	//get account info
	$query = 'SELECT * FROM anon_bank_transactions WHERE anon_id = '.$acc.' AND game_id = '.$game.' ORDER BY transaction_id';
	$db->query($query);
	if ($db->getNumRows() > 0)
	{
		$template->assign('PageTopic','ANON ACCOUNT '.$acc);
		$PHP_OUTPUT.= create_table();
		$PHP_OUTPUT.=('<tr><th align=center>Player Name</th><th align=center>Type</th><th align=center>Amount</th></tr>');
		while ($db->nextRecord())
		{
			$db2->query('SELECT * FROM player WHERE account_id = ' . $db->getField('account_id'));
			$db2->nextRecord();
			$PHP_OUTPUT.=('<tr><td align=center>');
			$PHP_OUTPUT.=$db2->getField('player_name');
			$PHP_OUTPUT.=('</td><td align=center>');
			$PHP_OUTPUT.=$db->getField('transaction');
			$PHP_OUTPUT.=('</td><td align=center>');
			$PHP_OUTPUT.=$db->getField('amount');
			$PHP_OUTPUT.=('</td></tr>');
			
		}
		$PHP_OUTPUT.=('</table>');
		
	} else $PHP_OUTPUT.=('Account '.$acc.' in game '.$game.' does NOT exist!');
	
}

?>