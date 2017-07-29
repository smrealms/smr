<?php

//view anon acct activity.
$template->assign('PageTopic','View Anonymous Account Info');
//do we have an acct?
if (empty($_REQUEST['anon_account'])||empty($_REQUEST['game_id'])) {

	$container = array();
	$container['url'] = 'skeleton.php';
	$container['body'] = 'anon_acc_view.php';
	$PHP_OUTPUT.=('What account would you like to view?<br />');
	$PHP_OUTPUT.=create_echo_form($container);
	$PHP_OUTPUT.=('Account ID: <input type="number" name="anon_account" /><br />');
	$PHP_OUTPUT.=('Game ID: <input type="number" name="game_id" /><br />');
	$PHP_OUTPUT.=create_submit('Continue');
	$PHP_OUTPUT.=('</form>');

}
else {
	//db object
	$db2 = new SmrMySqlDatabase();
	//split the name
	$acc = $_REQUEST['anon_account'];
	$game = $_REQUEST['game_id'];
	//get account info
	$db->query('SELECT *
				FROM anon_bank_transactions
				WHERE anon_id = '.$db->escapeNumber($acc).'
					AND game_id = '.$db->escapeNumber($game).'
				ORDER BY transaction_id');
	if ($db->getNumRows() > 0) {
		$PHP_OUTPUT.="<p>Transactions from Anonymous Account $acc in Game $game</p>";
		$PHP_OUTPUT.= create_table();
		$PHP_OUTPUT.=('<tr><th align=center>Player Name</th><th align=center>Type</th><th align=center>Amount</th></tr>');
		while ($db->nextRecord()) {
			$db2->query('SELECT player_name FROM player WHERE account_id=' . $db2->escapeNumber($db->getInt('account_id')) . ' AND game_id=' . $db2->escapeNumber($game));
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
