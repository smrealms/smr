<?php

// ********************************
// *
// * V a l i d a t e d ?
// *
// ********************************

// is account validated?
if ($account->validated == 'FALSE') {
	$PHP_OUTPUT.=create_error('You are not validated so you cannot use banks.');
	return;
}
if (isset($var['alliance_id'])) $alliance_id = $var['alliance_id'];
else $alliance_id = $player->getAllianceID();
$template->assign('PageTopic','Bank');

include(get_file_loc('menue.inc'));
$PHP_OUTPUT.=create_bank_menue();

$db->query('SELECT sum(amount) as total FROM alliance_bank_transactions WHERE alliance_id = '.$alliance_id.' AND game_id = '.$player->getGameID().' AND ' . 
		'payee_id = '.$player->getAccountID().' AND transaction = \'Payment\'');
if ($db->nextRecord()) $playerWith = $db->getField('total');
else $playerWith = 0;
$db->query('SELECT sum(amount) as total FROM alliance_bank_transactions WHERE alliance_id = '.$alliance_id.' AND game_id = '.$player->getGameID().' AND ' . 
		'payee_id = '.$player->getAccountID().' AND transaction = \'Deposit\'');
if ($db->nextRecord()) $playerDep = $db->getField('total');
else $playerDep = 0;
$differential = $playerDep - $playerWith;
$db->query('SELECT * FROM alliance_treaties WHERE game_id = '.$player->getGameID().
			' AND (alliance_id_1 = '.$player->getAllianceID().' OR alliance_id_2 = '.$player->getAllianceID().')'.
			' AND aa_access = 1 AND official = \'TRUE\'');
$temp=array();
while ($db->nextRecord()) {
	if ($db->getField('alliance_id_1') == $player->getAllianceID())
		$temp[$db->getField('alliance_id_2')] = $db->getField('alliance_id_1');
	else $temp[$db->getField('alliance_id_1')] = $db->getField('alliance_id_2');
}
$tempAllIDs = array_keys($temp);
if (sizeof($tempAllIDs)) {
	$tempAllIDs[] = $player->getAllianceID();
	$temp[$player->getAllianceID()] = $player->getAllianceID();
	$db->query('SELECT alliance_name, alliance_id FROM alliance WHERE alliance_id IN (' . implode(',',$tempAllIDs) . ') AND game_id = '.$player->getGameID());
	while ($db->nextRecord()) $alliances[$db->getField('alliance_id')] = stripslashes($db->getField('alliance_name'));
	if (sizeof($temp) > 0) {
		$container=array();
		$container['url'] = 'skeleton.php';
		$container['body'] = 'bank_alliance.php';
		$PHP_OUTPUT.=('<ul>');
		foreach ($temp as $alliedID => $myID) {
			$container['alliance_id'] = $alliedID;
			$PHP_OUTPUT.=('<li>');
			$PHP_OUTPUT.=create_link($container,'<span class="bold">' . $alliances[$alliedID] . '\'s Account</span>');
			$PHP_OUTPUT.=('</li>');
		}
		$PHP_OUTPUT.=('</ul><br />');
	}
}
$PHP_OUTPUT.= 'Hello ';
$PHP_OUTPUT.= $player->getPlayerName();
$PHP_OUTPUT.= ',<br />';
if ($alliance_id == $player->getAllianceID()) {
	$db->query('SELECT * FROM player_has_alliance_role WHERE account_id = '.$player->getAccountID().' AND game_id = '.$player->getGameID().' AND alliance_id='.$alliance_id);
	if ($db->nextRecord()) $role_id = $db->getField('role_id');
	else $role_id = 0;
	$query = 'role_id = '.$role_id;
} else
	$query = 'role = ' . $db->escape_string($player->getAllianceName());
$db->query('SELECT * FROM alliance_has_roles WHERE alliance_id = ' . $alliance_id . ' AND game_id = ' . $player->getGameID() . ' AND ' . $query);
$db->nextRecord();
$exempt = $db->getField('exempt_with') == 'TRUE';
if ($db->getField('with_per_day') == -2) $PHP_OUTPUT.=('You can withdraw an unlimited amount from this account. <br />');
elseif ($db->getField('with_per_day') == -1) $PHP_OUTPUT.=('You can only withdraw ' . number_format($differential) . ' more credits based on your deposits.<br />');
else {
	$perDay = $db->getField('with_per_day');
	$PHP_OUTPUT.=('You can withdraw up to ' . number_format($db->getField('with_per_day')) . ' credits per 24 hours.<br />');
	$db->query('SELECT sum(amount) as total FROM alliance_bank_transactions WHERE alliance_id = '.$alliance_id.' AND game_id = '.$player->getGameID().' AND ' . 
			'payee_id = '.$player->getAccountID().' AND transaction = \'Payment\' AND exempt = 0 AND time > ' . (TIME - 24 * 60 * 60));
	$PHP_OUTPUT.=('So far you have withdrawn ');
	$remaining = $perDay;
	if ($db->nextRecord() && !is_null($db->getField('total'))) {
		$PHP_OUTPUT.=(number_format($db->getField('total')));
		$remaining -= $db->getField('total');
	} else $PHP_OUTPUT.=('0');
	$PHP_OUTPUT.=(' credits in the past 24 hours.  You can withdraw ' . number_format($remaining) . ' more credits.<br />');
}
if (isset($_REQUEST['maxValue'])
	&& is_numeric($_REQUEST['maxValue'])
	&& $_REQUEST['maxValue'] > 0
) {
	$maxValue = $_REQUEST['maxValue'];
}
else {
	$db->query('SELECT MAX(transaction_id) FROM alliance_bank_transactions
				WHERE game_id=' . $player->getGameID() . '
				AND alliance_id=' . $alliance_id
				);
	if($db->nextRecord()) {
		$maxValue = $db->getField('MAX(transaction_id)');
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
WHERE alliance_bank_transactions.game_id=' . $player->getGameID() . '
AND player.game_id=' . $player->getGameID() . '
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
if ($db->getNumRows() > 0) {

	$PHP_OUTPUT.= '<div align="center">';
 
	$container = array();
	$container['url'] = 'skeleton.php';
	$container['body'] = 'bank_alliance.php';
	$container['alliance_id'] = $alliance_id;
	$form = create_form($container,'Show');
	$PHP_OUTPUT.= $form['form'];
	$PHP_OUTPUT.= '<table cellspacing="5" cellpadding="0" class="nobord"><tr><td>';
	$PHP_OUTPUT.= '<input class="center" type="text" name="minValue" size="3" value="' . $minValue . '">';
	$PHP_OUTPUT.= '</td><td>-</td><td>';
	$PHP_OUTPUT.= '<input class="center" type="text" name="maxValue" size="3" value="' . $maxValue . '">';
	$PHP_OUTPUT.= '</td><td>';
	$PHP_OUTPUT.= $form['submit'];
	$PHP_OUTPUT.= '</td></tr></table></form>';
	$PHP_OUTPUT.= '<table class="standard inset"><tr><th>#</th><th>Date</th><th>Trader</th><th>Reason for transfer</th><th>Withdrawal</th><th>&nbsp;&nbsp;Deposit&nbsp;&nbsp</th>';
	if ($exempt) $PHP_OUTPUT.=('<th>Make Exempt</th>');
	$PHP_OUTPUT.= '</tr>';

	$container = array();
	$container['url']		= 'skeleton.php';
	$container['body']		= 'trader_search_result.php';
	$formContainer=array();
	$formContainer['url'] = 'bank_alliance_exempt_processing.php';
	$formContainer['body'] = '';
	$formContainer['minVal'] = $minValue;
	$formContainer['maxVal'] = $maxValue;
	$form = create_form($formContainer,'Make Exempt');
	$PHP_OUTPUT.= $form['form'];
	while ($db->nextRecord()) {
		$PHP_OUTPUT.= '<tr><td class="center shrink">';
		$PHP_OUTPUT.= $db->getField('transaction_id');
		$PHP_OUTPUT.= '</td><td class="shrink center noWrap">';
		$PHP_OUTPUT.= date(DATE_FULL_SHORT_SPLIT, $db->getField('time'));
		$PHP_OUTPUT.= '</td><td>';
		if ($db->getField('exempt')) $PHP_OUTPUT.= 'Alliance Funds c/o<br />';
		$container['player_id']	= $db->getField('player_id');
		$PHP_OUTPUT.=create_link($container, get_colored_text($db->getField('alignment'),stripslashes($db->getField('player_name'))));
		$PHP_OUTPUT.= '</td><td>';
		$PHP_OUTPUT.= stripslashes($db->getField('reason'));
		$PHP_OUTPUT.= '</td><td class="shrink right">';
		if ($db->getField('transaction') == 'Payment') $PHP_OUTPUT.= (number_format($db->getField('amount')));
		else $PHP_OUTPUT.= '&nbsp;';
		$PHP_OUTPUT.= '</td><td class="shrink right">';
		if ($db->getField('transaction') == 'Deposit') $PHP_OUTPUT.=(number_format($db->getField('amount')));
		else $PHP_OUTPUT.= '&nbsp;';
		$PHP_OUTPUT.= '</td>';
		if ($exempt) {
			$PHP_OUTPUT.=('<td style="text-align:center;"><input type="checkbox" name="exempt[' . $db->getField('transaction_id') . '] value="true"');
			if ($db->getField('exempt')) $PHP_OUTPUT.=(' checked');
			$PHP_OUTPUT.=('></td>');
		}
		$PHP_OUTPUT.= '</tr>';
	}

	$db->query('SELECT alliance_account FROM alliance
				WHERE alliance_id=' . $alliance_id  . '
				AND game_id=' . $player->getGameID() . ' LIMIT 1'
				);
	$db->nextRecord();
	$PHP_OUTPUT.= '<tr>';
	$PHP_OUTPUT.= '<th colspan="5" class="right">Ending Balance</th><td class="bold shrink right">';
	$PHP_OUTPUT.= number_format($db->getField('alliance_account'));
	$PHP_OUTPUT.= '</td>';
	if ($exempt) $PHP_OUTPUT.=('<td>' . $form['submit'] . '</td>');
	$PHP_OUTPUT.= '</tr></form></table></div>';
}
else {
	$PHP_OUTPUT.= 'Your alliance account is still unused<br />';
}
//TODO: location of report button
$container=array();
$container['url'] = 'skeleton.php';
$container['body'] = 'bank_report.php';
$container['alliance_id'] = $alliance_id;
$PHP_OUTPUT.=('<div align="center">');
$form = create_form($container,'View Bank Report');
$PHP_OUTPUT.= $form['form'];
$PHP_OUTPUT.= $form['submit'];
$PHP_OUTPUT.=('</form>');
$PHP_OUTPUT.=('</div>');

$container=array();
$container['url'] = 'bank_alliance_processing.php';
$container['body'] = '';
$container['alliance_id'] = $alliance_id;
$actions = array();
$actions[] = array('Deposit','Deposit');
$actions[] = array('Withdraw','Withdraw');
$form = create_form($container,$actions);

$PHP_OUTPUT.= $form['form'];
$PHP_OUTPUT.= '
<h2>Make transaction</h2><br />
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
<br />';


$PHP_OUTPUT.= $form['submit']['Deposit'];
$PHP_OUTPUT.= '&nbsp;&nbsp;';
$PHP_OUTPUT.= $form['submit']['Withdraw'];

$PHP_OUTPUT.= '</form>';

?>