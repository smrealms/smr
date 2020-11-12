<?php declare(strict_types=1);
if (!isset($var['alliance_id'])) {
	SmrSession::updateVar('alliance_id', $player->getAllianceID());
}
$alliance_id = $var['alliance_id'];

$amount = Request::getInt('amount');

// no negative amounts are allowed
if ($amount <= 0) {
	create_error('You must actually enter an amount > 0!');
}
$message = Request::get('message');
if (empty($message)) {
	$message = 'No reason specified';
}

$alliance = SmrAlliance::getAlliance($alliance_id, $player->getGameID());
$action = Request::get('action');
if ($action == 'Deposit') {
	if ($player->getCredits() < $amount) {
		create_error('You don\'t own that much money!');
	}

	$amount = $alliance->increaseBank($amount); // handles overflow
	$player->decreaseCredits($amount);
	if ($alliance->getBank() >= MAX_MONEY) {
		$message .= ' (Account is Full)';
	}

} else {
	$action = 'Payment';
	if ($alliance->getBank() < $amount) {
		create_error('Your alliance isn\'t that rich!');
	}
	$query = '';
	if ($alliance_id == $player->getAllianceID()) {
		$role_id = $player->getAllianceRole($alliance_id);
		$query = 'role_id = ' . $db->escapeNumber($role_id);
	} else {
		// Alliance treaties create new roles with alliance names
		$query = 'role = ' . $db->escapeString($player->getAlliance()->getAllianceName());
	}
	$db->query('SELECT * FROM alliance_has_roles WHERE alliance_id = ' . $db->escapeNumber($alliance_id) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND ' . $query);
	$db->requireRecord();
	$withdrawalPerDay = $db->getInt('with_per_day');
	if ($db->getBoolean('positive_balance')) {
		$db->query('SELECT transaction, sum(amount) as total FROM alliance_bank_transactions
			WHERE alliance_id = ' . $db->escapeNumber($alliance->getAllianceID()) . ' AND ' . $player->getSQL() . '
			GROUP BY transaction');
		$playerTrans = array('Deposit' => 0, 'Payment' => 0);
		while ($db->nextRecord()) {
			$playerTrans[$db->getField('transaction')] = $db->getInt('total');
		}
		$allowedWithdrawal = $withdrawalPerDay + $playerTrans['Deposit'] - $playerTrans['Payment'];
		if ($allowedWithdrawal - $amount < 0) {
			create_error('Your alliance won\'t allow you to take so much with how little you\'ve given!');
		}
	} elseif ($withdrawalPerDay >= 0) {
		$db->query('SELECT sum(amount) as total FROM alliance_bank_transactions
					WHERE alliance_id = ' . $db->escapeNumber($alliance_id) . '
						AND ' . $player->getSQL() . '
						AND transaction = \'Payment\'
						AND exempt = 0
						AND time > ' . $db->escapeNumber(TIME - 86400));
		if ($db->nextRecord() && !is_null($db->getInt('total'))) {
			$total = $db->getInt('total');
		} else {
			$total = 0;
		}
		if ($total + $amount > $withdrawalPerDay) {
			create_error('Your alliance doesn\'t allow you to take that much cash this often!');
		}
	}

	$amount = $player->increaseCredits($amount); // handles overflow
	$alliance->decreaseBank($amount);
}

// log action
$player->log(LOG_TYPE_BANK, $action . ' ' . $amount . ' credits for alliance account of ' . $alliance->getAllianceName());

// get next transaction id
$db->query('SELECT MAX(transaction_id) as next_id FROM alliance_bank_transactions
			WHERE alliance_id = ' . $db->escapeNumber($alliance_id) . '
				AND game_id = ' . $db->escapeNumber($player->getGameID()));
if ($db->nextRecord()) {
	$next_id = $db->getInt('next_id') + 1;
}

// save log
$requestExempt = Request::has('requestExempt') ? 1 : 0;
$db->query('INSERT INTO alliance_bank_transactions
			(alliance_id, game_id, transaction_id, time, player_id, reason, transaction, amount, request_exempt)
			VALUES(' . $db->escapeNumber($alliance_id) . ', ' . $db->escapeNumber($player->getGameID()) . ', ' . $db->escapeNumber($next_id) . ', ' . $db->escapeNumber(TIME) . ', ' . $db->escapeNumber($player->getPlayerID()) . ', ' . $db->escapeString($message) . ', ' . $db->escapeString($action) . ', ' . $db->escapeNumber($amount) . ', ' . $db->escapeNumber($requestExempt) . ')');

// update player credits
$player->update();

// save money for alliance
$alliance->update();

$container = create_container('skeleton.php', 'bank_alliance.php');
$container['alliance_id'] = $alliance_id;
forward($container);
