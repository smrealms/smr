<?php


// ********************************
// *
// * V a l i d a t e d ?
// *
// ********************************

// is account validated?
if ($account->validated == "FALSE") {

	print_error("You are not validated so you can't use banks.");
	return;

}
$account_num = $_REQUEST['account_num'];
$make = $var["make"];
$made = $var["made"];

if (isset($var["account_num"]))
	$account_num = $var["account_num"];

if (isset($var["password"]))
	$pass = $var["password"];

if (isset($var["amount"]))
	$amount = $var["amount"];

if (!isset($account_num))
	$topic = 'Anonymous Account';
else
	$topic = 'Anonymous Account #' . $account_num;
print_topic($topic);

include(get_file_loc('menue.inc'));
print_bank_menue();

if (isset($make)) {

	echo 'Hello ';
	echo $player->player_name;
	echo '<br><br>';

	echo '<h2>Create Account</h2><br>';

	echo 'Please enter the password you would like<br><br>';
	$container = array();
    $container["url"] = "skeleton.php";
    $container["body"] = "bank_anon.php";
    $container["made"] = "yes";
    $form = create_form($container,'Create Account');
	echo $form['form'];
	echo '
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
	<br>
	';
	echo $form['submit'];
	echo '</form>';
}

if (isset($made)) {
	echo 'Hello ';
	echo $player->player_name;
	echo '<br><br>';

	$password = $_REQUEST['password'];
	$verify_pass = $_REQUEST['verify_pass'];

    if ($password != $verify_pass) {
    	print_error("The passwords do NOT match");
        return;
    }

	$db->query("SELECT MAX(anon_id) FROM anon_bank WHERE game_id = ".SmrSession::$game_id);
    if ($db->next_record())
	    $new_acc = $db->f("MAX(anon_id)") + 1;
    else
    	$new_acc = 1;
    $db->query("INSERT INTO anon_bank (game_id, anon_id, owner_id, password, amount) VALUES (".SmrSession::$game_id.", $new_acc, $player->account_id, '$password', 0)");
    echo 'Account #';
	echo $new_acc;
	echo ' has been opened for you.<br><br>';


}

$container = array();
$container['url'] = 'skeleton.php';

if (!isset($account_num) && !isset($make)) {
	echo 'Hello ';
	echo $player->player_name;
	echo '<br><br>';

	echo '<h2>Access accounts</h2><br>';
    $container["body"] = "bank_anon.php";
	$form = create_form($container,'Access Account');
	echo $form['form'];
	echo '
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
	<br>';

	echo $form['submit'];
	echo '</form>';

	$db->query('SELECT * FROM anon_bank 
				WHERE owner_id=' . $player->account_id . '
				AND game_id=' . $player->game_id);
	if ($db->nf()) {
		echo '<br><h2>Your accounts</h2><br>';
		echo '<div align=center>';
		echo '<table cellspacing="0" cellpadding="0" class="standard inset" ><tr><th>ID</th><th>Password</th><th>Last Transaction</th><th>Balance</th><th>Option</th></tr>';
	
		$container = array();
		$container["url"] = "skeleton.php";
		$container["body"] = "bank_anon.php";
		$db2 = new SmrMySqlDatabase();
		while ($db->next_record()) {
			$db2->query('SELECT MAX(time) FROM anon_bank_transactions
						WHERE game_id=' . $player->game_id . '
						AND anon_id=' . $db->f('anon_id') . ' LIMIT 1');

			echo '<tr><td class="shrink center">';
			echo $db->f("anon_id");
			echo '</td><td>';
			echo $db->f("password");
			echo '</td><td class="shrink nowrap">';

			if($db2->next_record() && $db2->f('MAX(time)')) {
				echo date('n/j/Y g:i:s A', $db2->f('MAX(time)'));
			}
			else {
				echo 'No transactions';
			}
		
			echo '</td><td class="right shrink">';
			echo $db->f("amount");
			echo '</td><td class="button">';
        	$container["account_num"] = $db->f("anon_id");
        	$container["password"] = $db->f("password");
        	print_button($container, 'Access Account');
			echo '</td></tr>';
    	}
		echo '</table></div><br>';
	}

	$container = array();
	$container['url'] = 'skeleton.php';
	$container['body'] = 'bank_anon.php';
	$container['make'] = 'Yes';
	print_button($container,'Create an account');
}

if (isset($account_num)) {

	//they didnt come from the creation screen so we need to check if the pw is correct
    $db->query('SELECT *
				FROM anon_bank
				WHERE anon_id=' . $account_num . '
				AND game_id=' . $player->game_id . ' LIMIT 1'
				);

	if($db->next_record()) {
		if ($var['allowed'] != 'yes') {
    		if (isset($_REQUEST['pass'])) $pass = $_REQUEST['pass'];
    		else $pass = $var['password'];

			if ($db->f('password') != $pass) {

				print_error('Invalid password.');
				return;
			}
		}
	}
	else {
		print_error("This account does not exist");
       	return;
	}

	$balance = $db->f('amount');
	$password= $db->f('password');

	echo 'Hello ';
	echo $player->player_name;
	echo '<br>';

	if (isset($_REQUEST['maxValue'])
		&& is_numeric($_REQUEST['maxValue'])
		&& $_REQUEST['maxValue'] > 0
	) {
		$maxValue = $_REQUEST['maxValue'];
	}
	else {
		$db->query('SELECT MAX(transaction_id) FROM anon_bank_transactions
					WHERE game_id=' . $player->game_id . '
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
	WHERE anon_bank_transactions.game_id=' . $player->game_id . '
	AND player.game_id=' . $player->game_id . '
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

		echo '<div align="center">';
 
		$container = array();
		$container['url'] = 'skeleton.php';
		$container['body'] = 'bank_anon.php';
		$container['allowed'] = 'yes';
		$container['account_num'] = $account_num;
		$form = create_form($container,'Show');
		echo $form['form'];
		echo '<table cellspacing="5" cellpadding="0" class="nobord"><tr><td>';
		echo '<input class="center" type="text" name="minValue" size="3" value="' . $minValue . '">';
		echo '</td><td>-</td><td>';
		echo '<input class="center" type="text" name="maxValue" size="3" value="' . $maxValue . '">';
		echo '</td><td>';
		echo $form['submit'];
		echo '</td></tr></table></form>';
		echo '<table cellspacing="0" cellpadding="0"class="standard inset"><tr><th>#</th><th>Date</th><th>Trader</th><th>Withdrawal</th><th>&nbsp;&nbsp;Deposit&nbsp;&nbsp</th></tr>';

		$container = array();
		$container["url"]		= "skeleton.php";
		$container["body"]		= "trader_search_result.php";

		while ($db->next_record()) {
			echo '<tr><td class="shrink center">';
			echo $db->f('transaction_id');
			echo '</td><td class="shrink center nowrap">';
			echo date('n/j/Y\<b\r /\>g:i:s A', $db->f('time'));
			echo '</td><td>';
			$container["player_id"]	= $db->f('player_id');
			print_link($container, get_colored_text($db->f('alignment'),stripslashes($db->f('player_name'))));
			echo '</td><td class="shrink right">';
			if ($db->f('transaction') == 'Payment') echo (number_format($db->f('amount')));
			else echo '&nbsp;';
			echo '</td><td class="shrink right">';
			if ($db->f('transaction') == 'Deposit') print(number_format($db->f('amount')));
			else echo '&nbsp;';
			echo '</td></tr>';
		}

		echo '<tr>';
		echo '<th colspan="4" class="right">Ending Balance</th><td class="bold shrink right">';
		echo number_format($balance);
		echo '</td></tr></table></div>';
	}
	else {
		echo '<br>No transactions have been made on this account.<br>';
	}

	echo '<br>';
	echo '<h2>Make transaction</h2><br>';
	$container=array();
	$container['url'] = 'bank_anon_processing.php';
    $container['password'] = $password;
    $container['account_num'] = $account_num;
	$actions = array();
	$actions[] = array('Deposit','Deposit');
	$actions[] = array('Withdraw','Withdraw');
	$form = create_form($container,$actions);

	echo $form['form'];
	echo 'Amount:&nbsp;<input type="text" name="amount" size="10" value="0"><br><br>';

	echo $form['submit']['Deposit'];
	echo '&nbsp;&nbsp;';
	echo $form['submit']['Withdraw'];

	echo '</form>';

}

?>