<?php

// ********************************
// *
// * V a l i d a t e d ?
// *
// ********************************

// is account validated?
if ($account->validated == 'FALSE') {
	print_error('You are not validated so you can\'t use banks.');
	return;
}
if (isset($var['alliance_id'])) $alliance_id = $var['alliance_id'];
else $alliance_id = $player->alliance_id;
print_topic('Bank');

include(get_file_loc('menue.inc'));
print_bank_menue();

$db->query("SELECT sum(amount) as total FROM alliance_bank_transactions WHERE alliance_id = $alliance_id AND game_id = $player->game_id AND " . 
		"payee_id = $player->account_id AND transaction = 'Payment'");
if ($db->next_record()) $playerWith = $db->f("total");
else $playerWith = 0;
$db->query("SELECT sum(amount) as total FROM alliance_bank_transactions WHERE alliance_id = $alliance_id AND game_id = $player->game_id AND " . 
		"payee_id = $player->account_id AND transaction = 'Deposit'");
if ($db->next_record()) $playerDep = $db->f("total");
else $playerDep = 0;
$differential = $playerDep - $playerWith;
$db->query("SELECT * FROM alliance_treaties WHERE game_id = $player->game_id 
			AND (alliance_id_1 = $player->alliance_id OR alliance_id_2 = $player->alliance_id)
			AND aa_access = 1 AND official = 'TRUE'");
$temp=array();
while ($db->next_record()) {
	if ($db->f("alliance_id_1") == $player->alliance_id)
		$temp[$db->f("alliance_id_2")] = $db->f("alliance_id_1");
	else $temp[$db->f("alliance_id_1")] = $db->f("alliance_id_2");
}
$tempAllIDs = array_keys($temp);
if (sizeof($tempAllIDs)) {
	$tempAllIDs[] = $player->alliance_id;
	$temp[$player->alliance_id] = $player->alliance_id;
	$db->query("SELECT alliance_name, alliance_id FROM alliance WHERE alliance_id IN (" . implode(',',$tempAllIDs) . ") AND game_id = $player->game_id");
	while ($db->next_record()) $alliances[$db->f("alliance_id")] = stripslashes($db->f("alliance_name"));
	if (sizeof($temp) > 0) {
		$container=array();
		$container['url'] = 'skeleton.php';
		$container['body'] = 'bank_alliance.php';
		print("<ul>");
		foreach ($temp as $alliedID => $myID) {
			$container['alliance_id'] = $alliedID;
			print("<li>");
			print_link($container,"<span style=\"font-weight:bold;\">" . $alliances[$alliedID] . "'s Account</span>");
			print("</li>");
		}
		print("</ul><br />");
	}
}
echo 'Hello ';
echo $player->player_name;
echo ',<br />';
if ($alliance_id == $player->alliance_id) {
	$db->query("SELECT * FROM player_has_alliance_role WHERE account_id = $player->account_id AND game_id = $player->game_id AND alliance_id=$player->alliance_id");
	if ($db->next_record()) $role_id = $db->f("role_id");
	else $role_id = 0;
	$query = "role_id = $role_id";
} else
	$query = 'role = "' . addslashes(addslashes($player->alliance_name)) . '"';
$db->query('SELECT * FROM alliance_has_roles WHERE alliance_id = ' . $alliance_id . ' AND game_id = ' . $player->game_id . ' AND ' . $query);
$db->next_record();
$exempt = $db->f("exempt_with");
if ($db->f("with_per_day") == -2) print("You can withdraw an unlimited amount from this account. <br />");
elseif ($db->f("with_per_day") == -1) print("You can only withdraw " . number_format($differential) . " more credits based on your deposits.<br />");
else {
	$perDay = $db->f("with_per_day");
	print("You can withdraw up to " . number_format($db->f("with_per_day")) . " credits per 24 hours.<br />");
	$db->query("SELECT sum(amount) as total FROM alliance_bank_transactions WHERE alliance_id = $alliance_id AND game_id = $player->game_id AND " . 
			"payee_id = $player->account_id AND transaction = 'Payment' AND exempt = 0 AND time > " . (time() - 24 * 60 * 60));
	print("So far you have withdrawn ");
	$remaining = $perDay;
	if ($db->next_record() && !is_null($db->f("total"))) {
		print(number_format($db->f("total")));
		$remaining -= $db->f("total");
	} else print("0");
	print(" credits in the past 24 hours.  You can withdraw " . number_format($remaining) . " more credits.<br />");
}
if (isset($_REQUEST['maxValue'])
	&& is_numeric($_REQUEST['maxValue'])
	&& $_REQUEST['maxValue'] > 0
) {
	$maxValue = $_REQUEST['maxValue'];
}
else {
	$db->query('SELECT MAX(transaction_id) FROM alliance_bank_transactions
				WHERE game_id=' . $player->game_id . '
				AND alliance_id=' . $alliance_id
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
alliance_bank_transactions.time as time,
player.player_name as player_name,
player.player_id as player_id,
player.alignment as alignment,
alliance_bank_transactions.transaction_id as transaction_id,
alliance_bank_transactions.transaction as transaction,
alliance_bank_transactions.amount as amount,
alliance_bank_transactions.exempt as exempt,
alliance_bank_transactions.reason as reason
FROM alliance_bank_transactions,player
WHERE alliance_bank_transactions.game_id=' . $player->game_id . '
AND player.game_id=' . $player->game_id . '
AND alliance_bank_transactions.alliance_id=' . $alliance_id  . '
AND player.account_id = alliance_bank_transactions.payee_id';


if($maxValue > 0 && $minValue > 0) {
	$query .= ' AND alliance_bank_transactions.transaction_id>=' . $minValue;
	$query .= ' AND alliance_bank_transactions.transaction_id<=' . $maxValue;
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
	$container['body'] = 'bank_alliance.php';
	$container['alliance_id'] = $alliance_id;
	$form = create_form($container,'Show');
	echo $form['form'];
	echo '<table cellspacing="5" cellpadding="0" class="nobord"><tr><td>';
	echo '<input class="center" type="text" name="minValue" size="3" value="' . $minValue . '">';
	echo '</td><td>-</td><td>';
	echo '<input class="center" type="text" name="maxValue" size="3" value="' . $maxValue . '">';
	echo '</td><td>';
	echo $form['submit'];
	echo '</td></tr></table></form>';
	echo '<table cellspacing="0" cellpadding="0" class="standard inset"><tr><th>#</th><th>Date</th><th>Trader</th><th>Reason for transfer</th><th>Withdrawal</th><th>&nbsp;&nbsp;Deposit&nbsp;&nbsp</th>';
	if ($exempt) print("<th>Make Exempt</th>");
	echo '</tr>';

	$container = array();
	$container["url"]		= "skeleton.php";
	$container["body"]		= "trader_search_result.php";
	$formContainer=array();
	$formContainer['url'] = "bank_alliance_exempt_processing.php";
	$formContainer['body'] = "";
	$formContainer['minVal'] = $minValue;
	$formContainer['maxVal'] = $maxValue;
	$form = create_form($formContainer,'Make Exempt');
	echo $form['form'];
	while ($db->next_record()) {
		echo '<tr><td class="center shrink">';
		echo $db->f('transaction_id');
		echo '</td><td class="shrink center nowrap">';
		echo date('n/j/Y\<b\r /\>g:i:s A', $db->f('time'));
		echo '</td><td>';
		if ($db->f("exempt")) echo 'Alliance Funds c/o<br />';
		$container["player_id"]	= $db->f('player_id');
		print_link($container, get_colored_text($db->f('alignment'),stripslashes($db->f('player_name'))));
		echo '</td><td>';
		echo stripslashes($db->f('reason'));
		echo '</td><td class="shrink right">';
		if ($db->f('transaction') == 'Payment') echo (number_format($db->f('amount')));
		else echo '&nbsp;';
		echo '</td><td class="shrink right">';
		if ($db->f('transaction') == 'Deposit') print(number_format($db->f('amount')));
		else echo '&nbsp;';
		echo '</td>';
		if ($exempt) {
			print("<td style=\"text-align:center;\"><input type=\"checkbox\" name=\"exempt[" . $db->f("transaction_id") . "] value=\"true\"");
			if ($db->f("exempt")) print(" checked");
			print("></td>");
		}
		echo '</tr>';
	}

	$db->query('SELECT alliance_account FROM alliance
				WHERE alliance_id=' . $alliance_id  . '
				AND game_id=' . $player->game_id . ' LIMIT 1'
				);
	$db->next_record();
	echo '<tr>';
	echo '<th colspan="5" class="right">Ending Balance</th><td class="bold shrink right">';
	echo number_format($db->f('alliance_account'));
	echo '</td>';
	if ($exempt) print("<td>" . $form['submit'] . "</td>");
	echo '</tr></form></table></div>';
}
else {
	echo 'Your alliance account is still unused<br>';
}
//TODO: location of report button
$container=array();
$container['url'] = 'skeleton.php';
$container['body'] = 'bank_report.php';
$container['alliance_id'] = $alliance_id;
print("<div align=\"center\">");
$form = create_form($container,'View Bank Report');
echo $form['form'];
echo $form['submit'];
print("</form>");
print("</div>");

$container=array();
$container['url'] = 'bank_alliance_processing.php';
$container['body'] = '';
$container['alliance_id'] = $alliance_id;
$actions = array();
$actions[] = array('Deposit','Deposit');
$actions[] = array('Withdraw','Withdraw');
$form = create_form($container,$actions);

echo $form['form'];
echo '
<h2>Make transaction</h2><br>
<table cellspacing="0" cellpadding="0" class="nobord nohpad">
	<tr>
		<td class="top">Amount:&nbsp;</td>
		<td><input type="text" name="amount" size="10">&nbsp;
			Request Exemption:<input type="checkbox" name="requestExempt"></td>
	</tr>
	<tr>
		<td class="top">Reason:&nbsp;</td>
		<td><textarea name="message"></textarea></td>
	</tr>
</table>
<br>';


echo $form['submit']['Deposit'];
echo '&nbsp;&nbsp;';
echo $form['submit']['Withdraw'];

echo '</form>';

?>