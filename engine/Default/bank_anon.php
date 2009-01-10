<?


// ********************************
// *
// * V a l i d a t e d ?
// *
// ********************************

// is account validated?
if ($account->validated == 'FALSE') {

	create_error('You are not validated so you cannot use banks.');
	return;

}
$account_num = $_REQUEST['account_num'];
$make = $var['make'];
$made = $var['made'];

if (isset($var['account_num']))
	$account_num = $var['account_num'];

if (isset($var['password']))
	$pass = $var['password'];

if (isset($var['amount']))
	$amount = $var['amount'];

if (!isset($account_num))
	$topic = 'Anonymous Account';
else
	$topic = 'Anonymous Account #' . $account_num;
$smarty->assign('PageTopic',$topic);

include(ENGINE . 'global/menue.inc');
$PHP_OUTPUT.=create_bank_menue();

if (isset($make)) {

	$PHP_OUTPUT.= 'Hello ';
	$PHP_OUTPUT.= $player->getPlayerName();
	$PHP_OUTPUT.= '<br /><br />';

	$PHP_OUTPUT.= '<h2>Create Account</h2><br />';

	$PHP_OUTPUT.= 'Please enter the password you would like<br /><br />';
	$container = array();
    $container['url'] = 'skeleton.php';
    $container['body'] = 'bank_anon.php';
    $container['made'] = 'yes';
    $form = create_form($container,'Create Account');
	$PHP_OUTPUT.= $form['form'];
	$PHP_OUTPUT.= '
	<table cellspacing="0" cellpadding="0" class="nobord nohpad">
		<tr>
			<td class="top">Password:&nbsp;</td>
			<td><input type="password" name="password" size="30"></td>
		</tr>
		<tr>
			<td class="top">Verify:&nbsp;</td>
			<td><input type="password" name="verify_pass" size="30"></td>
		</tr>
	</table>
	<br />
	';
	$PHP_OUTPUT.= $form['submit'];
	$PHP_OUTPUT.= '</form>';
}

if (isset($made)) {
	$PHP_OUTPUT.= 'Hello ';
	$PHP_OUTPUT.= $player->getPlayerName();
	$PHP_OUTPUT.= '<br /><br />';

	$password = $_REQUEST['password'];
	$verify_pass = $_REQUEST['verify_pass'];

    if ($password != $verify_pass) {
    	create_error('The passwords do NOT match');
        return;
    }

	$db->query('SELECT MAX(anon_id) FROM anon_bank WHERE game_id = '.SmrSession::$game_id);
    if ($db->next_record())
	    $new_acc = $db->f('MAX(anon_id)') + 1;
    else
    	$new_acc = 1;
    $db->query('INSERT INTO anon_bank (game_id, anon_id, owner_id, password, amount) VALUES ('.SmrSession::$game_id.', '.$new_acc.', '.$player->getAccountID().', '.$db->escapeString($password).', 0)');
    $PHP_OUTPUT.= 'Account #';
	$PHP_OUTPUT.= $new_acc;
	$PHP_OUTPUT.= ' has been opened for you.<br /><br />';


}

$container = array();
$container['url'] = 'skeleton.php';

if (!isset($account_num) && !isset($make)) {
	$PHP_OUTPUT.= 'Hello ';
	$PHP_OUTPUT.= $player->getPlayerName();
	$PHP_OUTPUT.= '<br /><br />';

	$PHP_OUTPUT.= '<h2>Access accounts</h2><br />';
    $container['body'] = 'bank_anon.php';
	$form = create_form($container,'Access Account');
	$PHP_OUTPUT.= $form['form'];
	$PHP_OUTPUT.= '
	<table cellspacing="0" cellpadding="0" class="nobord nohpad">
		<tr>
			<td class="top">Account Number:&nbsp;</td>
			<td><input type="text" name="account_num" size="4" value="0"></td>
		</tr>
		<tr>
			<td class="top">Password:&nbsp;</td>
			<td><input type="password" name="pass" size="30"></td>
		</tr>
	</table>
	<br />';

	$PHP_OUTPUT.= $form['submit'];
	$PHP_OUTPUT.= '</form>';

	$db->query('SELECT * FROM anon_bank 
				WHERE owner_id=' . $player->getAccountID() . '
				AND game_id=' . $player->getGameID());
	if ($db->nf()) {
		$PHP_OUTPUT.= '<br /><h2>Your accounts</h2><br />';
		$PHP_OUTPUT.= '<div align=center>';
		$PHP_OUTPUT.= '<table cellspacing="0" cellpadding="0" class="standard inset" ><tr><th>ID</th><th>Password</th><th>Last Transaction</th><th>Balance</th><th>Option</th></tr>';
	
		$container = array();
		$container['url'] = 'skeleton.php';
		$container['body'] = 'bank_anon.php';
		$db2 = new SMR_DB();
		while ($db->next_record()) {
			$db2->query('SELECT MAX(time) FROM anon_bank_transactions
						WHERE game_id=' . $player->getGameID() . '
						AND anon_id=' . $db->f('anon_id') . ' LIMIT 1');

			$PHP_OUTPUT.= '<tr><td class="shrink center">';
			$PHP_OUTPUT.= $db->f('anon_id');
			$PHP_OUTPUT.= '</td><td>';
			$PHP_OUTPUT.= $db->f('password');
			$PHP_OUTPUT.= '</td><td class="shrink nowrap">';

			if($db2->next_record() && $db2->f('MAX(time)')) {
				$PHP_OUTPUT.= date('n/j/Y g:i:s A', $db2->f('MAX(time)'));
			}
			else {
				$PHP_OUTPUT.= 'No transactions';
			}
		
			$PHP_OUTPUT.= '</td><td class="right shrink">';
			$PHP_OUTPUT.= $db->f('amount');
			$PHP_OUTPUT.= '</td><td class="button">';
        	$container['account_num'] = $db->f('anon_id');
        	$container['password'] = $db->f('password');
        	$PHP_OUTPUT.=create_button($container, 'Access Account');
			$PHP_OUTPUT.= '</td></tr>';
    	}
		$PHP_OUTPUT.= '</table></div><br /><br />';
	}

	$container = array();
	$container['url'] = 'skeleton.php';
	$container['body'] = 'bank_anon.php';
	$container['make'] = 'Yes';
	$PHP_OUTPUT.=create_button($container,'Create an account');
}

if (isset($account_num))
{
	//they didnt come from the creation screen so we need to check if the pw is correct
    $db->query('SELECT *
				FROM anon_bank
				WHERE anon_id=' . $account_num . '
				AND game_id=' . $player->getGameID() . ' LIMIT 1'
				);

	if($db->next_record())
	{
		if ($var['allowed'] != 'yes')
		{
    		if (isset($_REQUEST['pass'])) $pass = $_REQUEST['pass'];
    		else $pass = $var['password'];

			if ($db->f('password') != $pass)
			{
				$PHP_OUTPUT.=create_error('Invalid password');
				return;
			}
		}
	}
	else
	{
		$PHP_OUTPUT.=create_error('This account does not exist');
       	return;
	}

	$balance = $db->f('amount');
	$password= $db->f('password');

	$PHP_OUTPUT.= 'Hello ';
	$PHP_OUTPUT.= $player->getPlayerName();
	$PHP_OUTPUT.= '<br />';

	if (isset($_REQUEST['maxValue'])
		&& is_numeric($_REQUEST['maxValue'])
		&& $_REQUEST['maxValue'] > 0
	) {
		$maxValue = $_REQUEST['maxValue'];
	}
	else {
		$db->query('SELECT MAX(transaction_id) FROM anon_bank_transactions
					WHERE game_id=' . $player->getGameID() . '
					AND anon_id=' . $account_num
					);
		if($db->next_record()) {
			$maxValue = $db->f('MAX(transaction_id)');
			$minValue = $maxValue - 5;
			if($minValue < 1) {
				$minValue = 1;
			}
		}
		else{
			$minValue = 1;
			$maxValue = 5;
		}
	}

	if(isset($_REQUEST['minValue'])
		&& $_REQUEST['minValue'] <= $maxValue
		&& $_REQUEST['minValue'] > 0
		&& is_numeric($_REQUEST['maxValue'])
	) {
		$minValue = $_REQUEST['minValue'];
	}

	$query = '
	SELECT
	anon_bank_transactions.time as time,
	player.player_name as player_name,
	player.player_id as player_id,
	player.alignment as alignment,
	anon_bank_transactions.transaction_id as transaction_id,
	anon_bank_transactions.transaction as transaction,
	anon_bank_transactions.amount as amount
	FROM anon_bank_transactions,player
	WHERE anon_bank_transactions.game_id=' . $player->getGameID() . '
	AND player.game_id=' . $player->getGameID() . '
	AND anon_bank_transactions.anon_id=' . $account_num . '
	AND player.account_id = anon_bank_transactions.account_id';


	if($maxValue > 0 && $minValue > 0) {
		$query .= ' AND anon_bank_transactions.transaction_id>=' . $minValue;
		$query .= ' AND anon_bank_transactions.transaction_id<=' . $maxValue;
		$query .= ' ORDER BY time LIMIT ';
		$query .= (1 + $maxValue - $minValue);
	}
	else {
		$query .= ' ORDER BY time LIMIT 10';
	}

	$db->query($query);

	// only if we have at least one result
	if ($db->nf() > 0) {

		$PHP_OUTPUT.= '<div align="center">';
 
		$container = array();
		$container['url'] = 'skeleton.php';
		$container['body'] = 'bank_anon.php';
		$container['allowed'] = 'yes';
		$container['account_num'] = $account_num;
		$form = create_form($container,'Show');
		$PHP_OUTPUT.= $form['form'];
		$PHP_OUTPUT.= '<table cellspacing="5" cellpadding="0" class="nobord"><tr><td>';
		$PHP_OUTPUT.= '<input class="center" type="text" name="minValue" size="3" value="' . $minValue . '">';
		$PHP_OUTPUT.= '</td><td>-</td><td>';
		$PHP_OUTPUT.= '<input class="center" type="text" name="maxValue" size="3" value="' . $maxValue . '">';
		$PHP_OUTPUT.= '</td><td>';
		$PHP_OUTPUT.= $form['submit'];
		$PHP_OUTPUT.= '</td></tr></table></form>';
		$PHP_OUTPUT.= '<table cellspacing="0" cellpadding="0"class="standard inset"><tr><th>#</th><th>Date</th><th>Trader</th><th>Withdrawal</th><th>&nbsp;&nbsp;Deposit&nbsp;&nbsp</th></tr>';

		$container = array();
		$container['url']		= 'skeleton.php';
		$container['body']		= 'trader_search_result.php';

		while ($db->next_record()) {
			$PHP_OUTPUT.= '<tr><td class="shrink center">';
			$PHP_OUTPUT.= $db->f('transaction_id');
			$PHP_OUTPUT.= '</td><td class="shrink center nowrap">';
			$PHP_OUTPUT.= date('n/j/Y\<b\r /\>g:i:s A', $db->f('time'));
			$PHP_OUTPUT.= '</td><td>';
			$container['player_id']	= $db->f('player_id');
			$PHP_OUTPUT.=create_link($container, get_colored_text($db->f('alignment'),stripslashes($db->f('player_name'))));
			$PHP_OUTPUT.= '</td><td class="shrink right">';
			if ($db->f('transaction') == 'Payment') $PHP_OUTPUT.= (number_format($db->f('amount')));
			else $PHP_OUTPUT.= '&nbsp;';
			$PHP_OUTPUT.= '</td><td class="shrink right">';
			if ($db->f('transaction') == 'Deposit') $PHP_OUTPUT.=(number_format($db->f('amount')));
			else $PHP_OUTPUT.= '&nbsp;';
			$PHP_OUTPUT.= '</td></tr>';
		}

		$PHP_OUTPUT.= '<tr>';
		$PHP_OUTPUT.= '<th colspan="4" class="right">Ending Balance</th><td class="bold shrink right">';
		$PHP_OUTPUT.= number_format($balance);
		$PHP_OUTPUT.= '</td></tr></table></div>';
	}
	else {
		$PHP_OUTPUT.= '<br />No transactions have been made on this account.<br />';
	}

	$PHP_OUTPUT.= '<br />';
	$PHP_OUTPUT.= '<h2>Make transaction</h2><br />';
	$container=array();
	$container['url'] = 'bank_anon_processing.php';
    $container['password'] = $password;
    $container['account_num'] = $account_num;
	$actions = array();
	$actions[] = array('Deposit','Deposit');
	$actions[] = array('Withdraw','Withdraw');
	$form = create_form($container,$actions);

	$PHP_OUTPUT.= $form['form'];
	$PHP_OUTPUT.= 'Amount:&nbsp;<input type="text" name="amount" size="10" value="0"><br /><br />';

	$PHP_OUTPUT.= $form['submit']['Deposit'];
	$PHP_OUTPUT.= '&nbsp;&nbsp;';
	$PHP_OUTPUT.= $form['submit']['Withdraw'];

	$PHP_OUTPUT.= '</form>';

}

?>