<?
$close = $_REQUEST['close'];
if (isset($close)) {
	
	//get accs to close
	$reason = $_REQUEST['reason'];
	//never expire
	$expire_time = 0;
	$amount = 0;
	foreach ($close as $key => $value) {
		
		$val = 'Match list:';
		$val .= $value;
		$db->query('REPLACE INTO account_is_closed ' .
			   '(account_id, reason_id, expires, suspicion) ' .
			   'VALUES('.$key.', 2, '.$expire_time.', '.$db->escapeString($val).')');
		$db->query('INSERT INTO account_has_closing_history ' .
			   '(account_id, time, admin_id, action) ' .
			   'VALUES('.$key.', ' . TIME . ', '.SmrSession::$account_id.', \'Closed\')');

		$db->query('UPDATE player SET newbie_turns = 1 ' .
				   'WHERE account_id = '.$key.' AND ' .
						 'newbie_turns = 0 AND ' .
						 'land_on_planet = \'FALSE\'');
	
		$db->query('DELETE FROM active_session WHERE account_id = '.$key);
		$amount++;
	}
	
}
$first = $_REQUEST['first'];
if (isset($first)) {

	$val = 'Match list:';
	$a = 0;
	$same_ip = $_REQUEST['same_ip'];
	foreach ($same_ip as $account_id) {
		if ($a > 0)
			$val .= ',';
		$val .= $db->escapeString($account_id);
		$a++;
	}
	foreach ($same_ip as $account_id) {
		//never expire
		$expire_time = 0;
		$db->query('REPLACE INTO account_is_closed ' .
			   '(account_id, reason_id, expires, suspicion) ' .
			   'VALUES('.$account_id.', 2, '.$expire_time.', '.$db->escapeString($val).')');

		$db->query('INSERT INTO account_has_closing_history ' .
			   '(account_id, time, admin_id, action) ' .
			   'VALUES('.$account_id.', ' . TIME . ', '.SmrSession::$account_id.', \'Closed\')');

		$db->query('UPDATE player SET newbie_turns = 1 ' .
			   'WHERE account_id = '.$account_id.' AND ' .
					 'newbie_turns = 0 AND ' .
					 'land_on_planet = \'FALSE\'');

		$db->query('DELETE FROM active_session ' .
			   'WHERE account_id = '.$account_id);

		$curr_account =& SmrAccount::getAccount($account_id);
		$admin_id = SmrSession::$account_id;
		$admin_account =& SmrAccount::getAccount($admin_id);;
		$curr_account->log(13, 'Account closed by ' . $admin_account->login . '.');
	}
	$container = array();
	$container['url'] = 'skeleton.php';
	$container['body'] = 'game_play.php';
	forward($container);

}
$second = $_REQUEST['second'];
if (isset($second)) {
	//never expire
	$expire_time = 0;
	$db->query('REPLACE INTO account_is_closed ' .
		   '(account_id, reason_id, expires, suspicion) ' .
		   'VALUES('.$second.', 2, '.$expire_time.', \'Auto:By Admin\')');

	$db->query('INSERT INTO account_has_closing_history ' .
		   '(account_id, time, admin_id, action) ' .
		   'VALUES('.$second.', ' . TIME . ', '.SmrSession::$account_id.', \'Closed\')');

	$db->query('UPDATE player SET newbie_turns = 1 ' .
		   'WHERE account_id = '.$second.' AND ' .
				 'newbie_turns = 0 AND ' .
				 'land_on_planet = \'FALSE\'');

	$db->query('DELETE FROM active_session ' .
		   'WHERE account_id = '.$second);

	$curr_account =& SmrAccount::getAccount($second);
	$admin_id = SmrSession::$account_id;
	$admin_account =& SmrAccount::getAccount($admin_id);;
	$curr_account->log(13, 'Account closed by ' . $admin_account->login . '.');

}
$action = $_REQUEST['action'];
if($action == 'Next Page No Disable') {
	//we have to send back to ip check page now since we dont disable
	$container['variable'] = $variable;
	$container['last_ip'] = $last_ip;
	$container['last_acc'] = $last_acc;
	$container['total'] = $total;
	$container['type'] = $type;
	$closed_so_far = $_REQUEST['closed_so_far'];
	if (isset($closed_so_far))
		$container['closed_so_far'] = $closed_so_far;
	if (!isset($var['continue'])) {
		$container['url'] = 'skeleton.php';
		$container['body'] = 'game_play.php';
	} else {
		$container['url'] = 'skeleton.php';
		$container['body'] = 'ip_view_results.php';
	}
	forward($container);
}
$disable_id = $_REQUEST['disable_id'];
if (isset($disable_id)) {
	$suspicion = $_REQUEST['suspicion'];
	$suspicion2 = $_REQUEST['suspicion2'];
	foreach ($disable_id as $id) {

		// generate list of messages that should be deleted
		$reason = $suspicion[$id];
		if (empty($reason) || $reason == '')
			$reason = $suspicion2[$id];
	    $db->query('SELECT * FROM account_is_closed WHERE account_id = '.$id);
	    if (!$db->getNumRows())
	        $amount += 1;

		//never expire
		$expire_time = 0;
		$db->query('REPLACE INTO account_is_closed ' .
			   '(account_id, reason_id, expires, suspicion) ' .
			   'VALUES('.$id.', 2, '.$expire_time.', '.$db->escapeString($reason).')');

		$db->query('INSERT INTO account_has_closing_history ' .
			   '(account_id, time, admin_id, action) ' .
			   'VALUES('.$id.', ' . TIME . ', '.SmrSession::$account_id.', \'Closed\')');

		$db->query('UPDATE player SET newbie_turns = 1 ' .
			   'WHERE account_id = '.$id.' AND ' .
					 'newbie_turns = 0 AND ' .
					 'land_on_planet = \'FALSE\'');

		$db->query('DELETE FROM active_session ' .
			   'WHERE account_id = '.$id);
		$curr_account =& SmrAccount::getAccount($id);
		$admin_id = SmrSession::$account_id;
		$admin_account =& SmrAccount::getAccount($admin_id);
		$curr_account->log(13, 'Account closed by ' . $admin_account->login . '.');

	}
}
if (isset($_REQUEST['amount'])) $amount = $_REQUEST['amount'];
if (!isset($amount))
	$amount = 0;
$closed_so_far = $_REQUEST['closed_so_far'];
if (isset($closed_so_far))
	$amount += $closed_so_far;
$msg = 'You have disabled '.$amount.' accounts.';
if ($amount > 20)
    $msg .= '  How do you sleep at night ;)';
$container = array();
$container['url'] = 'skeleton.php';
$container['type'] = $type;
$container['last_ip'] = $last_ip;
$container['last_acc'] = $last_acc;
$container['total'] = $total;
if (isset($var['continue'])) {
	//we have to send back to ip check page
	$container['variable'] = $variable;
	$container['closed_so_far'] = $amount;
	$container['body'] = 'ip_view_results.php';
} else {
	$container['body'] = 'game_play.php';
	$container['msg'] = $msg;
}
forward($container);
?>