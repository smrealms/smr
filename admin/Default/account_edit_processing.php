<?

//we are gonna check for reducing points...
$db2 = new SMR_DB();
$db->lock('account_has_points');
$week = time() - (7 * 24 * 60 * 60);
$db->query('SELECT * FROM account_has_points WHERE last_update < '.$week.' AND points > 0 AND points < 100');
while ($db->next_record()) {

	$acc_id = $db->f('account_id');
	$last_update = $db->f('last_update');
	$last_update += 7 * 24 * 60 * 60;
	$db2->query('UPDATE account_has_points SET points = points - 1, last_update = '.$last_update.' WHERE account_id = '.$acc_id);

}
$db->unlock();

$account_id = $var['account_id'];
$curr_account =& SmrAccount::getAccount($account_id);

// request
$donation = $_REQUEST['donation'];
$smr_credit = $_REQUEST['smr_credit'];
$choise = $_REQUEST['choise'];
$reason_pre_select = $_REQUEST['reason_pre_select'];
$reason_msg = $_REQUEST['reason_msg'];
$veteran_status = $_REQUEST['veteran_status'];
$logging_status = $_REQUEST['logging_status'];
$except = $_REQUEST['exception_add'];
$names = $_REQUEST['player_name'];
$points = intval($_REQUEST['points']);
$delete = $_REQUEST['delete'];

$msg = 'You ';

if (!empty($donation)) {

	// add entry to account donated table
	$db->query('INSERT INTO account_donated (account_id, time, amount) VALUES ('.$account_id.', ' . time() . ' , '.$donation.')');

    // add the credits to the players account - if requested
    if (!empty($smr_credit)) {

	    $db->query('SELECT * FROM account_has_credits WHERE account_id = '.$account_id);
	    if ($db->next_record())
	    	$amount_credits = $db->f('credits_left');
	    else
	    	$amount_credits = 0;
	    $new_amount = $amount_credits + $donation;
	    $db->query('REPLACE INTO account_has_credits (account_id, credits_left) VALUES ('.$account_id.', '.$new_amount.')');

	}

	$msg .= 'added $'.$donation;

}

if ($choise == 'pre_select' && $points > 0) {

	//do we have points
	$db->query('SELECT * FROM account_has_points WHERE account_id = '.$account_id);
	if ($db->next_record())	{

		$now = $db->f('points');
		$tot_points = $points + $now;
		$db->query('UPDATE account_has_points SET points = '.$tot_points.', last_update = '.TIME.' WHERE account_id = '.$account_id);

	} else {

		$tot_points = $points;
		$db->query('REPLACE INTO account_has_points (account_id, points, last_update) VALUES ('.$account_id.', '.$points.', '.TIME.')');

	}

	if ($tot_points < 9) {
		//leave scripts its only a warning
		break;
	} elseif ($tot_points < 19) {
		$expire_time = 2 * 24 * 60 * 60;
	} elseif ($tot_points < 29) {
		$expire_time = 4 * 24 * 60 * 60;
	} elseif ($tot_points < 49) {
		$expire_time = 7 * 24 * 60 * 60;
	} elseif ($tot_points < 74) {
		$expire_time = 14 * 24 * 60 * 60;
	} elseif ($tot_points < 99) {
		$expire_time = 31 * 24 * 60 * 60;
	} elseif ($tot_points >= 100) {
		$expire_time = 0;
	}

	if ($expire_time > 0) {
		$days = $expire_time / 60 / 60 / 24;
		$expire_time += time();
		$expire_msg = 'for '.$days.' days';
	} else $expire_msg = 'forever!';
	$db->query('UPDATE account_has_points SET last_update = '.$expire_time.' WHERE account_id = '.$account_id);

	$suspicion = $_REQUEST['suspicion'];
	$db->query('REPLACE INTO account_is_closed ' .
			   '(account_id, reason_id, suspicion, expires) ' .
			   'VALUES('.$account_id.', '.$reason_pre_select.', '.$db->escapeString($suspicion).', '.$expire_time.')');

	$db->query('INSERT INTO account_has_closing_history ' .
			   '(account_id, time, admin_id, action) ' .
			   'VALUES('.$account_id.', ' . time() . ', '.SmrSession::$account_id.', \'Closed\')');

	$db->query('UPDATE player SET newbie_turns = 1 ' .
			   'WHERE account_id = '.$account_id.' AND ' .
					 'newbie_turns = 0 AND ' .
					 'land_on_planet = \'FALSE\'');
	$db->lock('active_session');
	$db->query('DELETE FROM active_session ' .
			   'WHERE account_id = '.$account_id);
	$db->unlock();
	if (strlen($msg) > 9)
		$msg .= 'and ';
	$msg .= 'closed ';

} elseif ($choise == 'individual' && $points > 0) {

	$db->query('INSERT INTO closing_reason (reason) VALUES(' . $db->escape_string($reason_msg) . ')');
	$reason_id = $db->insert_id();

	//do we have points
	$db->query('SELECT * FROM account_has_points WHERE account_id = '.$account_id);
	if ($db->next_record())	{

		$now = $db->f('points');
		$tot_points = $points + $now;
		$db->query('UPDATE account_has_points SET points = '.$tot_points.', last_update = '.TIME.' WHERE account_id = '.$account_id);

	} else {

		$tot_points = $points;
		$db->query('REPLACE INTO account_has_points (account_id, points, last_update) VALUES ('.$account_id.', '.$points.', '.TIME.')');

	}

	if ($tot_points < 9) {
		//leave scripts its only a warning
		break;
	} elseif ($tot_points < 19) {
		$expire_time = 2 * 24 * 60 * 60;
	} elseif ($tot_points < 29) {
		$expire_time = 4 * 24 * 60 * 60;
	} elseif ($tot_points < 49) {
		$expire_time = 7 * 24 * 60 * 60;
	} elseif ($tot_points < 74) {
		$expire_time = 14 * 24 * 60 * 60;
	} elseif ($tot_points < 99) {
		$expire_time = 31 * 24 * 60 * 60;
	} elseif ($tot_points >= 100) {
		$expire_time = 0;
	}

	if ($expire_time > 0) {
		$days = $expire_time / 60 / 60 / 24;
		$expire_time += time();
		$expire_msg = 'for '.$days.' days';
	} else $expire_msg = 'forever!';
	$db->query('UPDATE account_has_points SET last_update = '.$expire_time.' WHERE account_id = '.$account_id);

	$suspicion = $_REQUEST['suspicion'];
	$db->query('REPLACE INTO account_is_closed ' .
			   '(account_id, reason_id, suspicion, expires) ' .
			   'VALUES('.$account_id.', '.$reason_id.', '.$db->escapeString($suspicion).', '.$expire_time.')');

	$db->query('INSERT INTO account_has_closing_history ' .
			   '(account_id, time, admin_id, action) ' .
			   'VALUES('.$account_id.', ' . time() . ', '.SmrSession::$account_id.', \'Closed\')');

	$db->query('UPDATE player SET newbie_turns = 1 ' .
			   'WHERE account_id = '.$account_id.' AND ' .
					 'newbie_turns = 0 AND ' .
					 'land_on_planet = \'FALSE\'');
	$db->lock('active_session');
	$db->query('DELETE FROM active_session ' .
			   'WHERE account_id = '.$account_id);
	$db->unlock();
	if (strlen($msg) > 9)
		$msg .= 'and ';
	$msg .= 'closed ';

} elseif ($choise == 'reopen') {

	//do we have points
	$db->query('SELECT * FROM account_has_points WHERE account_id = '.$account_id);
	if ($db->next_record())	{

		$tot_points = $db->f('points') - $points;
		if ($tot_points < 0) $tot_points = 0;
		$db->query('UPDATE account_has_points SET points = '.$tot_points.', last_update = '.TIME.' WHERE account_id = '.$account_id);

	}
	$db->query('DELETE FROM account_is_closed ' .
			   'WHERE account_id = '.$account_id);

	$db->query('INSERT INTO account_has_closing_history ' .
			   '(account_id, time, admin_id, action) ' .
			   'VALUES('.$account_id.', ' . time() . ', '.SmrSession::$account_id.', \'Opened\')');

	if (strlen($msg) > 9)
		$msg .= 'and ';
	$msg .= 'reopened ';

}

if ($veteran_status != $curr_account->veteran) {

	$db->query('UPDATE account SET veteran = '.$db->escapeString($veteran_status).' WHERE account_id = '.$account_id);
	$msg .= 'set the veteran status to '.$db->escapeString($veteran_status).' ';

}

if ($logging_status != $curr_account->logging) {

	$db->query('UPDATE account SET logging = '.$db->escapeString($logging_status).' WHERE account_id = '.$account_id);
	$msg .= 'set the logging status to '.$db->escapeString($logging_status).' ';

}
if ($except != 'Add An Exception' && $except != '') {

	$db->query('INSERT INTO account_exceptions (account_id, reason) VALUES ('.$account_id.', '.$db->escapeString($except).')');
	$msg .= 'added the exception '.$except.' ';

}

if (!empty($names))
	foreach ($names as $game_id => $new_name)
	{
		if(!empty($new_name))
		{
			$db->query('SELECT * FROM player WHERE game_id = '.$game_id.' AND player_name = ' . $db->escape_string($new_name, FALSE));
			if (!$db->next_record()) {
				$db->query('SELECT player_name, player_id FROM player WHERE game_id='.$game_id.' AND account_id = '.$account_id.' LIMIT 1');
				$db->next_record();
				$old_name = $db->f('player_name');
				$player_id = $db->f('player_id');
				
				$db->query('UPDATE player SET player_name = ' . $db->escape_string($new_name, FALSE) . ' WHERE game_id = '.$game_id.' AND account_id = '.$account_id);
				$msg .= 'changed players name to '.$new_name.' ';
				//insert news message
	
				$news = '<span class="blue">ADMIN</span> Please be advised that <span class="yellow">' . $old_name . '(' . $player_id . ')</span> has had their name changed to <span class="yellow">' . $new_name . '(' . $player_id . ')</span>';
				
				$db->query('INSERT INTO news (time, news_message, game_id) VALUES (' . time() . ',' . $db->escape_string($news, FALSE) . ','.$game_id.')');
			}
		}
		
	}

if (!empty($delete)) {
	foreach ($delete as $game_id => $value) {
		if($value == 'TRUE') {
			// Check for bank transactions into the alliance account
			$db->query('SELECT * FROM alliance_bank_transactions WHERE payee_id=' . $account_id . ' AND game_id=' . $game_id . ' LIMIT 1');
			if($db->nf() != 0){
				// Can't delete
				$msg .= 'player has made alliance transaction ';
				continue;
			}
			// Check anon accounts for transactions
			$db->query('SELECT * FROM anon_bank_transactions WHERE account_id=' . $account_id . ' AND game_id=' . $game_id . ' LIMIT 1');
			if($db->nf() != 0){
				// Can't delete
				$msg .= 'player has made anonymous transaction ';
				continue;
			}

			$db->query('DELETE FROM alliance_thread WHERE sender_id=' . $account_id . ' AND game_id=' . $game_id);
			$db->query('DELETE FROM blackjack WHERE account_id=' . $account_id . ' AND game_id=' . $game_id);
			$db->query('DELETE FROM bounty WHERE account_id=' . $account_id . ' AND game_id=' . $game_id);
			$db->query('DELETE FROM force_refresh WHERE owner_id=' . $account_id . ' AND game_id=' . $game_id);
			$db->query('DELETE FROM galactic_post_applications WHERE account_id=' . $account_id . ' AND game_id=' . $game_id);
			$db->query('DELETE FROM galactic_post_article WHERE writer_id=' . $account_id . ' AND game_id=' . $game_id);
			$db->query('DELETE FROM galactic_post_writer WHERE account_id=' . $account_id . ' AND game_id=' . $game_id);
			$db->query('DELETE FROM irc_logged_in WHERE account_id=' . $account_id . ' AND game_id=' . $game_id);
			$db->query('DELETE FROM kills WHERE (dead_id=' . $account_id . ' OR killer_id=' . $account_id .') AND game_id=' . $game_id);
			$db->query('DELETE FROM message WHERE account_id=' . $account_id . ' AND game_id=' . $game_id);
			$db->query('DELETE FROM message_notify WHERE (from_id=' . $account_id . ' OR to_id=' . $account_id .') AND game_id=' . $game_id);
			$db->query('DELETE FROM message WHERE account_id=' . $account_id . ' AND game_id=' . $game_id);
			$db->query('UPDATE planet SET owner_id=0,planet_name=\'\',password=\'\',shields=0,drones=0,credits=0,bonds=0 WHERE owner_id=' . $account_id . ' AND game_id=' . $game_id);
			$db->query('DELETE FROM planet_attack WHERE trigger_id=' . $account_id . ' AND game_id=' . $game_id);
			$db->query('DELETE FROM player_attacks_planet WHERE account_id=' . $account_id . ' AND game_id=' . $game_id);
			$db->query('DELETE FROM player_attacks_port WHERE account_id=' . $account_id . ' AND game_id=' . $game_id);
			$db->query('DELETE FROM player_cache WHERE account_id=' . $account_id . ' AND game_id=' . $game_id);
			$db->query('DELETE FROM player_has_alliance_role WHERE account_id=' . $account_id . ' AND game_id=' . $game_id);
			$db->query('DELETE FROM player_has_drinks WHERE account_id=' . $account_id . ' AND game_id=' . $game_id);
			$db->query('DELETE FROM player_has_relation WHERE account_id=' . $account_id . ' AND game_id=' . $game_id);
			$db->query('DELETE FROM player_has_ticker WHERE account_id=' . $account_id . ' AND game_id=' . $game_id);
			$db->query('DELETE FROM player_has_ticket WHERE account_id=' . $account_id . ' AND game_id=' . $game_id);
			$db->query('DELETE FROM player_has_unread_messages WHERE account_id=' . $account_id . ' AND game_id=' . $game_id);
			$db->query('DELETE FROM player_is_president WHERE account_id=' . $account_id . ' AND game_id=' . $game_id);
			$db->query('DELETE FROM player_plotted_course WHERE account_id=' . $account_id . ' AND game_id=' . $game_id);
			$db->query('DELETE FROM player_read_thread WHERE account_id=' . $account_id . ' AND game_id=' . $game_id);
			$db->query('DELETE FROM player_visited_port WHERE account_id=' . $account_id . ' AND game_id=' . $game_id);
			$db->query('DELETE FROM player_visited_sector WHERE account_id=' . $account_id . ' AND game_id=' . $game_id);
			$db->query('DELETE FROM player_votes_pact WHERE account_id=' . $account_id . ' AND game_id=' . $game_id);
			$db->query('DELETE FROM player_votes_relation WHERE account_id=' . $account_id . ' AND game_id=' . $game_id);
			$db->query('DELETE FROM ship_has_cargo WHERE account_id=' . $account_id . ' AND game_id=' . $game_id);
			$db->query('DELETE FROM ship_has_hardware WHERE account_id=' . $account_id . ' AND game_id=' . $game_id);
			$db->query('DELETE FROM ship_has_illusion WHERE account_id=' . $account_id . ' AND game_id=' . $game_id);
			$db->query('DELETE FROM ship_has_name WHERE account_id=' . $account_id . ' AND game_id=' . $game_id);
			$db->query('DELETE FROM ship_has_weapon WHERE account_id=' . $account_id . ' AND game_id=' . $game_id);
			$db->query('DELETE FROM ship_is_cloaked WHERE account_id=' . $account_id . ' AND game_id=' . $game_id);
			$db->query('DELETE FROM player WHERE account_id=' . $account_id . ' AND game_id=' . $game_id);
			$db->query('DELETE FROM player_has_stats WHERE account_id=' . $account_id . ' AND game_id=' . $game_id);

			$db->query('UPDATE account_has_stats SET games_joined=games_joined-1 WHERE account_id=' . $account_id);

			$msg .= 'deleted player from game '.$game_id.' ';
		}
	}

}

//get his login name
$db->query('SELECT * FROM account WHERE account_id = '.$account_id);
if ($db->next_record())
	$login = $db->f('login');

$container = create_container('skeleton.php', 'account_edit.php');
$container['msg'] = $msg.' for the account of '.$login.' '.$expire_msg;

forward($container);

?>
