<?
$disable_account = $_REQUEST['disable_account'];
foreach ($disable_account as $curr_account_id) {

	// check if this one already has an entry
	$db->query('SELECT * FROM account_is_closed WHERE account_id = '.$curr_account_id);
	if ($db->getNumRows() == 0) {

		$db->query('INSERT INTO account_is_closed (account_id, reason_id, suspicion) VALUES('.$curr_account_id.', 2, \'double password\')');
		$db->query('INSERT INTO account_has_closing_history ' .
			   '(account_id, time, admin_id, action) ' .
			   'VALUES('.$curr_account_id.', ' . time() . ', '.SmrSession::$account_id.', \'Closed\')');

		$db->query('UPDATE player SET newbie_turns = 1 ' .
				   'WHERE account_id = '.$curr_account_id.' AND ' .
						 'newbie_turns = 0 AND ' .
						 'land_on_planet = \'FALSE\'');
	
		$db->query('DELETE FROM active_session WHERE account_id = '.$curr_account_id);
		$curr_account =& SmrAccount::getAccount($curr_account_id);
		$admin_id = SmrSession::$account_id;
		$admin_account =& SmrAccount::getAccount($admin_id);
		$curr_account->log(13, 'Account closed by ' . $admin_account->login . '.');

	}

}

forward(create_container('skeleton.php', 'game_play.php'))

?>