<?php
if (!isset($var['alliance_id'])) {
	SmrSession::updateVar('alliance_id',$player->getAllianceID());
}
$alliance_id = $var['alliance_id'];

$amount = $_REQUEST['amount'];
// check for numbers
if (!is_numeric($amount)) {
	create_error('Numbers only!');
}

// only whole numbers allowed
$amount = floor($amount);

// no negative amounts are allowed
if ($amount <= 0) {
	create_error('You must actually enter an amount > 0!');
}
$message = $_REQUEST['message'];
if (empty($message)) {
	$message = 'No reason specified';
}

$alliance = SmrAlliance::getAlliance($alliance_id, $player->getGameID());
$action = $_REQUEST['action'];
if ($action == 'Deposit') {
	if ($player->getCredits() < $amount) {
		create_error('You don\'t own that much money!');
	}

	$player->decreaseCredits($amount);
	$allianceCredits = $alliance->getAccount() + $amount;
	//too much money?
	if ($allianceCredits > 4294967295) {
		$overflow = $allianceCredits - 4294967295;
		$allianceCredits -= $overflow;
		$player->increaseCredits($overflow);
		$message .= ' (Account is Full)';
		$amount -= $overflow;
	}
	$alliance->setAccount($allianceCredits);
	// log action
	$account->log(LOG_TYPE_BANK, 'Deposits '.$amount.' credits in alliance account of '.$alliance->getAllianceName(), $player->getSectorID());
}
else {
	$action = 'Payment';
	if ($alliance->getAccount() < $amount) {
		create_error('Your alliance isn\'t soo rich!');
	}
	$query = '';
	if ($alliance_id == $player->getAllianceID()) {
		$role_id = $player->getAllianceRole($alliance_id);
		$query = 'role_id = ' . $db->escapeNumber($role_id);
	} else {
		// Alliance treaties create new roles with alliance names
		$query = 'role = ' . $db->escapeString($player->getAllianceName());
	}
	$db->query('SELECT * FROM alliance_has_roles WHERE alliance_id = ' . $db->escapeNumber($alliance_id) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND ' . $query);
	$db->nextRecord();
	$withdrawalPerDay = $db->getInt('with_per_day');
	if ($db->getBoolean('positive_balance')) {
		$db->query('SELECT transaction, sum(amount) as total FROM alliance_bank_transactions
			WHERE alliance_id = ' . $db->escapeNumber($alliance->getAllianceID()) . ' AND game_id = ' . $db->escapeNumber($alliance->getGameID()) . ' AND payee_id = ' . $db->escapeNumber($player->getAccountID()) . '
			GROUP BY transaction');
		$playerTrans = array('Deposit' => 0, 'Payment' => 0);
		while($db->nextRecord()) {
			$playerTrans[$db->getField('transaction')] = $db->getInt('total');
		}
		$allowedWithdrawal = $withdrawalPerDay + $playerTrans['Deposit'] - $playerTrans['Payment'];
		if ($allowedWithdrawal - $amount < 0) {
			create_error('Your alliance won\'t allow you to take so much with how little you\'ve given!');
		}
	}
	elseif ($withdrawalPerDay >= 0) {
		$db->query('SELECT sum(amount) as total FROM alliance_bank_transactions
					WHERE alliance_id = ' . $db->escapeNumber($alliance_id) . '
						AND game_id = ' . $db->escapeNumber($player->getGameID()) . '
						AND payee_id = ' . $db->escapeNumber($player->getAccountID()) . '
						AND transaction = \'Payment\'
						AND exempt = 0
						AND time > ' . $db->escapeNumber(TIME - 86400));
		if ($db->nextRecord() && !is_null($db->getField('total'))) {
			$total = $db->getInt('total');
		}
		else {
			$total = 0;
		}
		if ($total + $amount > $withdrawalPerDay) {
			create_error('Your alliance doesn\'t allow you to take that much cash this often!');
		}
	}

	$player->increaseCredits($amount);
	$allianceCredits = $alliance->getAccount() - $amount;
	//too much money?
	if ($player->getCredits() > 4294967295) {
		$overflow = $player->getCredits() - 4294967295;
		$allianceCredits += $overflow;
		$player->decreaseCredits($overflow);
		$amount += $overflow;
	}
	$alliance->setAccount($allianceCredits);

	// log action
	$account->log(LOG_TYPE_BANK, 'Takes '.$amount.' credits from alliance account of '.$alliance->getAllianceName(), $player->getSectorID());
}


// get next transaction id
$db->query('SELECT MAX(transaction_id) as next_id FROM alliance_bank_transactions
			WHERE alliance_id = ' . $db->escapeNumber($alliance_id) . '
				AND game_id = ' . $db->escapeNumber($player->getGameID()));
if ($db->nextRecord()) {
	$next_id = $db->getInt('next_id') + 1;
}

// save log
if (!empty($_REQUEST['requestExempt'])) {
	$requestExempt = 1;
}
else {
	$requestExempt = 0;
}
$db->query('INSERT INTO alliance_bank_transactions
			(alliance_id, game_id, transaction_id, time, payee_id, reason, transaction, amount, request_exempt)
			VALUES(' . $db->escapeNumber($alliance_id) . ', ' . $db->escapeNumber($player->getGameID()) . ', ' . $db->escapeNumber($next_id) . ', ' . $db->escapeNumber(TIME) . ', ' . $db->escapeNumber($player->getAccountID()) . ', ' . $db->escapeString($message) . ', '.$db->escapeString($action) . ', ' . $db->escapeNumber($amount) . ', ' . $db->escapeNumber($requestExempt) . ')');

// update player credits
$player->update();

// save money for alliance
$alliance->update();

$container = create_container('skeleton.php', 'bank_alliance.php');
$container['alliance_id'] = $alliance_id;
forward($container);
