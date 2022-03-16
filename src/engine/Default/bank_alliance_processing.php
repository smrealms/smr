<?php declare(strict_types=1);

$db = Smr\Database::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();

$alliance_id = $var['alliance_id'] ?? $player->getAllianceID();

$amount = Smr\Request::getInt('amount');

// no negative amounts are allowed
if ($amount <= 0) {
	create_error('You must actually enter an amount > 0!');
}
$message = Smr\Request::get('message');
if (empty($message)) {
	$message = 'No reason specified';
}

$alliance = SmrAlliance::getAlliance($alliance_id, $player->getGameID());
$action = Smr\Request::get('action');
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
	$dbResult = $db->read('SELECT * FROM alliance_has_roles WHERE alliance_id = ' . $db->escapeNumber($alliance_id) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND ' . $query);
	$dbRecord = $dbResult->record();
	$withdrawalPerDay = $dbRecord->getInt('with_per_day');
	if ($dbRecord->getBoolean('positive_balance')) {
		$dbResult = $db->read('SELECT transaction, sum(amount) as total FROM alliance_bank_transactions
			WHERE alliance_id = ' . $db->escapeNumber($alliance->getAllianceID()) . ' AND game_id = ' . $db->escapeNumber($alliance->getGameID()) . ' AND payee_id = ' . $db->escapeNumber($player->getAccountID()) . '
			GROUP BY transaction');
		$playerTrans = ['Deposit' => 0, 'Payment' => 0];
		foreach ($dbResult->records() as $dbRecord) {
			$playerTrans[$dbRecord->getField('transaction')] = $dbRecord->getInt('total');
		}
		$allowedWithdrawal = $withdrawalPerDay + $playerTrans['Deposit'] - $playerTrans['Payment'];
		if ($allowedWithdrawal - $amount < 0) {
			create_error('Your alliance won\'t allow you to take so much with how little you\'ve given!');
		}
	} elseif ($withdrawalPerDay >= 0) {
		$dbResult = $db->read('SELECT sum(amount) as total FROM alliance_bank_transactions
					WHERE alliance_id = ' . $db->escapeNumber($alliance_id) . '
						AND game_id = ' . $db->escapeNumber($player->getGameID()) . '
						AND payee_id = ' . $db->escapeNumber($player->getAccountID()) . '
						AND transaction = \'Payment\'
						AND exempt = 0
						AND time > ' . $db->escapeNumber(Smr\Epoch::time() - 86400));
		$total = 0;
		if ($dbResult->hasRecord()) {
			$total = $dbResult->record()->getInt('total');
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
$dbResult = $db->read('SELECT MAX(transaction_id) as next_id FROM alliance_bank_transactions
			WHERE alliance_id = ' . $db->escapeNumber($alliance_id) . '
				AND game_id = ' . $db->escapeNumber($player->getGameID()));
if ($dbResult->hasRecord()) {
	$next_id = $dbResult->record()->getInt('next_id') + 1;
}

// save log
$requestExempt = Smr\Request::has('requestExempt') ? 1 : 0;
$db->insert('alliance_bank_transactions', [
	'alliance_id' => $db->escapeNumber($alliance_id),
	'game_id' => $db->escapeNumber($player->getGameID()),
	'transaction_id' => $db->escapeNumber($next_id),
	'time' => $db->escapeNumber(Smr\Epoch::time()),
	'payee_id' => $db->escapeNumber($player->getAccountID()),
	'reason' => $db->escapeString($message),
	'transaction' => $db->escapeString($action),
	'amount' => $db->escapeNumber($amount),
	'request_exempt' => $db->escapeNumber($requestExempt),
]);

// update player credits
$player->update();

// save money for alliance
$alliance->update();

$container = Page::create('skeleton.php', 'bank_alliance.php');
$container['alliance_id'] = $alliance_id;
$container->go();
