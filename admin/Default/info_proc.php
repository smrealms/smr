<?

$action = $_REQUEST['action'];
if ($action == 'Reopen and Add Exception' || $action == 'Reopen without Exception') {
	
	$account_id = $_REQUEST['account_id'];
	$exception = $_REQUEST['exception'];
	if (!is_array($account_id))
		create_error('Please check the boxes next to the names you wish to open.');
	
	foreach ($account_id as $id) {
		
		$db->query('DELETE FROM account_is_closed WHERE account_id = '.$id);
		if ($action == 'Reopen and Add Exception') {
			
			$curr_exception = $exception[$id];
	        $db->query('REPLACE INTO account_exceptions (account_id, reason) ' .
	                        'VALUES ('.$id.', '.$db->escapeString($curr_exception).')');
	                        
		}
	}
	
} elseif ($action == 'Ban' || $action == 'Ban and remove exception') {

	$ban = $_REQUEST['ban'];
	$bancheck = $_REQUEST['bancheck'];
	//never expire
	$expire_time = 0;
	foreach ($bancheck as $id) {
	
		$db->query('REPLACE INTO account_is_closed ' .
			   '(account_id, reason_id, expires, suspicion) ' .
			   'VALUES('.$id.', 2, '.$expire_time.', '.$db->escapeString($ban[$id]).')');
	
		$db->query('INSERT INTO account_has_closing_history ' .
			   '(account_id, time, admin_id, action) ' .
			   'VALUES('.$id.', ' . TIME . ', '.SmrSession::$account_id.', \'Closed\')');
	
		$db->query('UPDATE player SET newbie_turns = 1 ' .
			   'WHERE account_id = '.$id.' AND ' .
					 'newbie_turns = 0 AND ' .
					 'land_on_planet = \'FALSE\'');
		$db->lockTable('active_session');
		$db->query('DELETE FROM active_session ' .
			   'WHERE account_id = '.$id);
		$db->unlock();
		if ($action == 'Ban and remove exception')
			$db->query('DELETE FROM account_exceptions WHERE account_id = '.$id);
		$curr_account =& SmrAccount::getAccount($id);
		$admin_id = SmrSession::$account_id;
		$admin_account =& SmrAccount::getAccount($admin_id);
		$curr_account->log(13, 'Account closed by ' . $admin_account->login . '.');
	}

}
$container = array();
$container['url'] = 'skeleton.php';
$container['body'] = 'game_play.php';
forward($container);

?>