<?php
if (isset($var['alliance_id'])) $alliance_id = $var['alliance_id'];
else $alliance_id = $player->getAllianceID();
require_once(get_file_loc('smr_alliance.inc'));
$amount = $_REQUEST['amount'];
// check for numbers
if (!is_numeric($amount))
	create_error('Numbers only please');

// only whole numbers allowed
$amount = floor($amount);

// no negative amounts are allowed
if ($amount <= 0)
	create_error('You must actually enter an amount > 0!');
$message = $_REQUEST['message'];
if (empty($message)) $message = 'No reason specified';

$alliance = new SMR_ALLIANCE($alliance_id, $player->getGameID());
$action = $_REQUEST['action'];
if ($action == 'Deposit') {

	if ($player->getCredits() < $amount)
		create_error('You don\'t own that much money!');

	$player->decreaseCredits($amount);
	$alliance->account += $amount;
	//too much money?
	if ($alliance->account > 4294967295) {
		
		$overflow = $alliance->account - 4294967295;
		$alliance->account -= $overflow;
		$player->increaseCredits($overflow);
		$message .= ' (Account is Full)';
		$amount -= $overflow;
		
	}
	// log action
	$account->log(4, 'Deposits '.$amount.' credits in alliance account of '.$alliance->getAllianceName(), $player->getSectorID());

} else {

	$action = 'Payment';

	if ($alliance->account < $amount)
		create_error('Your alliance isn\'t soo rich!');
	if ($alliance_id == $player->getAllianceID()) {
		$db->query('SELECT * FROM player_has_alliance_role WHERE account_id = '.$player->getAccountID().' AND game_id = '.$player->getGameID().' AND alliance_id='.$alliance_id);
		if ($db->nextRecord()) $role_id = $db->getField('role_id');
		else $role_id = 0;
		$query = 'role_id = '.$role_id;
	} else {
		$query = 'role = ' . $db->escape_string($player->getAllianceName());
	}
	$db->query('SELECT * FROM alliance_has_roles WHERE alliance_id = ' . $alliance_id . ' AND game_id = ' . $player->getGameID() . ' AND ' . $query);
	$db->nextRecord();
	if ($db->getField('with_per_day') == -1) {
		$db->query('SELECT sum(amount) as total FROM alliance_bank_transactions WHERE alliance_id = '.$alliance_id.' AND game_id = '.$player->getGameID().' AND ' .
				'payee_id = '.$player->getAccountID().' AND transaction = \'Payment\'');
		if ($db->nextRecord()) $playerWith = $db->getField('total');
		else $playerWith = 0;
		$db->query('SELECT sum(amount) as total FROM alliance_bank_transactions WHERE alliance_id = '.$alliance_id.' AND game_id = '.$player->getGameID().' AND ' .
				'payee_id = '.$player->getAccountID().' AND transaction = \'Deposit\'');
		if ($db->nextRecord()) $playerDep = $db->getField('total');
		else $playerDep = 0;
		$differential = $playerDep - $playerWith;
		if ($differential - $amount < 0) create_error('Your alliance won\'t allow you to take so much with how little you\'ve given!');
	} elseif ($db->getField('with_per_day') >= 0) {
		$max = $db->getField('with_per_day');
		$db->query('SELECT sum(amount) as total FROM alliance_bank_transactions WHERE alliance_id = '.$alliance_id.' AND game_id = '.$player->getGameID().' AND ' .
				'payee_id = '.$player->getAccountID().' AND transaction = \'Payment\' AND time > ' . (TIME - 24 * 60 * 60));
		if ($db->nextRecord() && !is_null($db->getField('total'))) $total = $db->getField('total');
		else $total = 0;
		if ($total + $amount > $max) create_error('Your alliance doesn\'t allow you to take that much cash this often');
	}
	
	$player->increaseCredits($amount);
	$alliance->account -= $amount;
	//too much money?
	if ($player->getCredits() > 4294967295) {
		
		$overflow = $player->getCredits() - 4294967295;
		$alliance->account += $overflow;
		$player->decreaseCredits($overflow);
		$amount += $overflow;
		
	}

	// log action
	$account->log(4, 'Takes '.$amount.' credits from alliance account of '.$alliance->getAllianceName(), $player->getSectorID());

}


// get next transaction id
$db->query('SELECT MAX(transaction_id) as next_id FROM alliance_bank_transactions ' .
		   'WHERE alliance_id = '.$alliance_id.' AND ' .
				 'game_id = '.$player->getGameID());
if ($db->nextRecord())
	$next_id = $db->getField('next_id') + 1;

// save log
if ($_REQUEST['requestExempt']) $requestExempt = 1;
else $requestExempt = 0;
$db->query('INSERT INTO alliance_bank_transactions ' .
		   '(alliance_id, game_id, transaction_id, time, payee_id, reason, transaction, amount, request_exempt) ' .
		   'VALUES('.$alliance_id.', '.$player->getGameID().', '.$next_id.', ' . TIME . ', '.$player->getAccountID().', ' . $db->escape_string($message, true) . ', '.$db->escapeString($action).', '.$amount.', '.$requestExempt.')');

// update player credits
$player->update();

// save money for alliance
$alliance->update();

$container = create_container('skeleton.php', 'bank_alliance.php');
$container['alliance_id'] = $alliance_id;
forward($container);

?>