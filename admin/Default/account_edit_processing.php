<?php declare(strict_types=1);

$account_id = $var['account_id'];
$curr_account = SmrAccount::getAccount($account_id);

// request
$donation = Request::getInt('donation');
$smr_credit = Request::has('smr_credit');
$rewardCredits = Request::getInt('grant_credits');
$choise = Request::get('choise', ''); // no radio button selected by default
$reason_pre_select = Request::getInt('reason_pre_select');
$reason_msg = Request::get('reason_msg');
$veteran_status = Request::get('veteran_status') == 'TRUE';
$logging_status = Request::get('logging_status') == 'TRUE';
$except = Request::get('exception_add');
$points = Request::getInt('points');
$names = Request::getArray('player_name', []); // missing when no games joined
$delete = Request::getArray('delete', []); // missing when no games joined

$actions = [];

if (!empty($donation)) {
	// add entry to account donated table
	$db->query('INSERT INTO account_donated (account_id, time, amount) VALUES (' . $db->escapeNumber($account_id) . ', ' . $db->escapeNumber(TIME) . ' , ' . $db->escapeNumber($donation) . ')');

	// add the credits to the players account - if requested
	if (!empty($smr_credit)) {
		$curr_account->increaseSmrCredits($donation * CREDITS_PER_DOLLAR);
	}

	$actions[] = 'added $' . $donation;
}

if (!empty($rewardCredits)) {
	$curr_account->increaseSmrRewardCredits($rewardCredits);
	$actions[] = 'added ' . $rewardCredits . ' reward credits';
}

if (Request::has('special_close')) {
	$specialClose = Request::get('special_close');
	// Make sure the special closing reason exists
	$db->query('SELECT reason_id FROM closing_reason WHERE reason=' . $db->escapeString($specialClose));
	if ($db->nextRecord()) {
		$reasonID = $db->getInt('reason_id');
	} else {
		$db->query('INSERT INTO closing_reason (reason) VALUES(' . $db->escapeString($specialClose) . ')');
		$reasonID = $db->getInsertID();
	}

	$closeByRequestNote = Request::get('close_by_request_note');
	if (empty($closeByRequestNote)) {
		$closeByRequestNote = $specialClose;
	}

	$curr_account->banAccount(0, $account, $reasonID, $closeByRequestNote);
	$actions[] = 'added ' . $specialClose . ' ban';
}

if ($choise == 'reopen') {
	//do we have points
	$curr_account->removePoints($points);
	$curr_account->unbanAccount($account);
	$actions[] = 'reopened account and removed ' . $points . ' points';
} elseif ($points > 0) {
	if ($choise == 'individual') {
		$db->query('INSERT INTO closing_reason (reason) VALUES(' . $db->escapeString($reason_msg) . ')');
		$reason_id = $db->getInsertID();
	} else {
		$reason_id = $reason_pre_select;
	}

	$suspicion = Request::get('suspicion');
	$bannedDays = $curr_account->addPoints($points, $account, $reason_id, $suspicion);
	$actions[] = 'added ' . $points . ' ban points';

	if ($bannedDays !== false) {
		if ($bannedDays > 0) {
			$expire_msg = 'for ' . $bannedDays . ' days';
		} else {
			$expire_msg = 'indefinitely';
		}
		$actions[] = 'closed ' . $expire_msg;
	}
}

if (Request::has('mailban')) {
	$mailban = Request::get('mailban');
	if ($mailban == 'remove') {
		$curr_account->setMailBanned(TIME);
		$actions[] = 'removed mailban';
	} elseif ($mailban == 'add_days') {
		$days = Request::getInt('mailban_days');
		$curr_account->increaseMailBanned($days * 86400);
		$actions[] = 'mail banned for ' . $days . ' days';
	}
}

if ($veteran_status != $curr_account->isVeteranForced()) {
	$db->query('UPDATE account SET veteran = ' . $db->escapeString($veteran_status) . ' WHERE account_id = ' . $db->escapeNumber($account_id));
	$actions[] = 'set the veteran status to ' . $db->escapeString($veteran_status);
}

if ($logging_status != $curr_account->isLoggingEnabled()) {
	$curr_account->setLoggingEnabled($logging_status);
	$actions[] = 'set the logging status to ' . $logging_status;
}

if ($except != 'Add An Exception' && $except != '') {
	$db->query('INSERT INTO account_exceptions (account_id, reason) VALUES (' . $db->escapeNumber($account_id) . ', ' . $db->escapeString($except) . ')');
	$actions[] = 'added the exception ' . $except;
}

if (!empty($names)) {
	foreach ($names as $game_id => $new_name) {
		if (!empty($new_name)) {
			// Escape html elements so the name displays correctly
			$new_name = htmlentities($new_name);

			$db->query('SELECT account_id FROM player WHERE game_id = ' . $db->escapeNumber($game_id) . ' AND player_name = ' . $db->escapeString($new_name));
			if (!$db->nextRecord()) {
				$editPlayer = SmrPlayer::getPlayer($account_id, $game_id);
				$editPlayer->setPlayerName($new_name);
				$editPlayer->update();

				$actions[] = 'changed players name to ' . $new_name;

				//insert news message
				$news = 'Please be advised that player ' . $editPlayer->getPlayerID() . ' has had their name changed to ' . $editPlayer->getBBLink();

				$db->query('INSERT INTO news (time, news_message, game_id, type) VALUES (' . $db->escapeNumber(TIME) . ',' . $db->escapeString($news) . ',' . $db->escapeNumber($game_id) . ', \'admin\')');
			} elseif ($db->getInt('account_id') != $account_id) {
				$actions[] = 'have NOT changed players name to ' . $new_name . ' (already taken)';
			}
		}

	}
}

if (!empty($delete)) {
	foreach ($delete as $game_id => $value) {
		if ($value == 'TRUE') {
			// Check for bank transactions into the alliance account
			$db->query('SELECT * FROM alliance_bank_transactions WHERE payee_id=' . $db->escapeNumber($account_id) . ' AND game_id=' . $db->escapeNumber($game_id) . ' LIMIT 1');
			if ($db->getNumRows() != 0) {
				// Can't delete
				$actions[] = 'player has made alliance transaction';
				continue;
			}
			// Check anon accounts for transactions
			$db->query('SELECT * FROM anon_bank_transactions WHERE account_id=' . $db->escapeNumber($account_id) . ' AND game_id=' . $db->escapeNumber($game_id) . ' LIMIT 1');
			if ($db->getNumRows() != 0) {
				// Can't delete
				$actions[] = 'player has made anonymous transaction';
				continue;
			}

			$sql = 'account_id=' . $db->escapeNumber($account_id) . ' AND game_id=' . $db->escapeNumber($game_id);
			$db->query('DELETE FROM alliance_thread
						WHERE sender_id=' . $db->escapeNumber($account_id) . ' AND game_id=' . $db->escapeNumber($game_id));
			$db->query('DELETE FROM bounty WHERE ' . $sql);
			$db->query('DELETE FROM galactic_post_applications WHERE ' . $sql);
			$db->query('DELETE FROM galactic_post_article
						WHERE writer_id=' . $db->escapeNumber($account_id) . ' AND game_id=' . $db->escapeNumber($game_id));
			$db->query('DELETE FROM galactic_post_writer WHERE ' . $sql);
			$db->query('DELETE FROM message WHERE ' . $sql);
			$db->query('DELETE FROM message_notify
						WHERE (from_id=' . $db->escapeNumber($account_id) . ' OR to_id=' . $db->escapeNumber($account_id) . ') AND game_id=' . $db->escapeNumber($game_id));
			$db->query('UPDATE planet SET owner_id=0,planet_name=\'\',password=\'\',shields=0,drones=0,credits=0,bonds=0
						WHERE owner_id=' . $db->escapeNumber($account_id) . ' AND game_id=' . $db->escapeNumber($game_id));
			$db->query('DELETE FROM player_attacks_planet WHERE ' . $sql);
			$db->query('DELETE FROM player_attacks_port WHERE ' . $sql);
			$db->query('DELETE FROM player_has_alliance_role WHERE ' . $sql);
			$db->query('DELETE FROM player_has_drinks WHERE ' . $sql);
			$db->query('DELETE FROM player_has_relation WHERE ' . $sql);
			$db->query('DELETE FROM player_has_ticker WHERE ' . $sql);
			$db->query('DELETE FROM player_has_ticket WHERE ' . $sql);
			$db->query('DELETE FROM player_has_unread_messages WHERE ' . $sql);
			$db->query('DELETE FROM player_plotted_course WHERE ' . $sql);
			$db->query('DELETE FROM player_read_thread WHERE ' . $sql);
			$db->query('DELETE FROM player_visited_port WHERE ' . $sql);
			$db->query('DELETE FROM player_visited_sector WHERE ' . $sql);
			$db->query('DELETE FROM player_votes_pact WHERE ' . $sql);
			$db->query('DELETE FROM player_votes_relation WHERE ' . $sql);
			$db->query('DELETE FROM ship_has_cargo WHERE ' . $sql);
			$db->query('DELETE FROM ship_has_hardware WHERE ' . $sql);
			$db->query('DELETE FROM ship_has_illusion WHERE ' . $sql);
			$db->query('DELETE FROM ship_has_weapon WHERE ' . $sql);
			$db->query('DELETE FROM ship_is_cloaked WHERE ' . $sql);
			$db->query('DELETE FROM player WHERE ' . $sql);

			$db->query('UPDATE active_session SET game_id=0 WHERE ' . $sql . ' LIMIT 1');

			$actions[] = 'deleted player from game ' . $game_id;
		}
	}

}

//get his login name
$container = create_container('skeleton.php', 'account_edit_search.php');
$container['msg'] = 'You ' . join(' and ', $actions) . ' for the account of ' . $curr_account->getLogin() . '.';

$curr_account->update();
forward($container);
