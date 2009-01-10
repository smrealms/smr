<?

//view anon acct activity.
$smarty->assign('PageTopic','VIEW ANON ACCOUNT INFO');
//do we have an acct?
if (empty($_REQUEST['acct_game'])) {

	$container = array();
	$container['url'] = 'skeleton.php';
	$container['body'] = 'anon_acc_view.php';
	$PHP_OUTPUT.=('What account would you like to view? (account_id/game_id)<br />');
	$PHP_OUTPUT.=create_echo_form($container);
	$PHP_OUTPUT.=('<input type=text name=acct_game value="120/5">');
	$PHP_OUTPUT.=create_submit('Continue');
	$PHP_OUTPUT.=('</form>');
	
} else {

	//db object
	$db2 = new SMR_DB();
	//split the name
	list ($acc, $game) = split('/',$_REQUEST['acct_game']);
	//get account info
	$query = 'SELECT * FROM anon_bank_transactions WHERE anon_id = '.$acc.' AND game_id = '.$game.' ORDER BY transaction_id';
	$db->query($query);
	if ($db->nf() > 0) {
		
		$smarty->assign('PageTopic','ANON ACCOUNT '.$acc);
		echo_table();
		$PHP_OUTPUT.=('<tr><th align=center>Player Name</th><th align=center>Type</th><th align=center>Amount</th></tr>');
		while ($db->next_record()) {
			
			$db2->query('SELECT * FROM player WHERE account_id = ' . $db->f('account_id'));
			$db2->next_record();
			$PHP_OUTPUT.=('<tr><td align=center>');
			$db2->p('player_name');
			$PHP_OUTPUT.=('</td><td align=center>');
			$db->p('transaction');
			$PHP_OUTPUT.=('</td><td align=center>');
			$db->p('amount');
			$PHP_OUTPUT.=('</td></tr>');
			
		}
		$PHP_OUTPUT.=('</table>');
		
	} else $PHP_OUTPUT.=('Account '.$acc.' in game '.$game.' does NOT exist!');
	
}

?>