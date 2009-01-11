<?

//first message
if (isset($_REQUEST['offenderReply'])) $offenderReply = $_REQUEST['offenderReply'];

if (isset($offenderReply) && $offenderReply != '') {
	$game_id = $var['game_id'];
	$message = $db->escape_string($offenderReply, false);
	
	$db->query('INSERT INTO message (account_id, game_id, message_type_id, message_text, sender_id, send_time) ' .
	                             'VALUES('.$var['offender'].', '.$game_id.', '.MSG_ADMIN.', '.$message.', 0, '.TIME.')');
	
	// give him the message icon
	$db->query('REPLACE INTO player_has_unread_messages (game_id, account_id, message_type_id) VALUES
				('.$game_id.', '.$var['offender'].', '.MSG_ADMIN.')');
	
	//do we have points?
	if ($_REQUEST['offenderBanPoints'])
	{
		$reasonID = 7;
		$db->query('SELECT * FROM account_has_points WHERE account_id = '.$var['offender']);
		if ($db->nextRecord())	{
	
			$currPoints = $db->getField('points');
			$newPoints = $currPoints + $_REQUEST['offenderBanPoints'];
			$db->query('UPDATE account_has_points SET points = '.$newPoints.', last_update = '.TIME.' WHERE account_id = '.$var['offender']);
	
		} else {
	
			$newPoints = $_REQUEST['offenderBanPoints'];
			$db->query('REPLACE INTO account_has_points (account_id, points, last_update) VALUES ('.$var['offender'].', '.$newPoints.', '.TIME.')');
	
		}
	
		// < 9 = warning
		if ($newPoints < 9) {
			$expire_time = -1;
		} elseif ($newPoints < 19) {
			$expire_time = 2 * 24 * 60 * 60;
		} elseif ($newPoints < 29) {
			$expire_time = 4 * 24 * 60 * 60;
		} elseif ($newPoints < 49) {
			$expire_time = 7 * 24 * 60 * 60;
		} elseif ($newPoints < 74) {
			$expire_time = 14 * 24 * 60 * 60;
		} elseif ($newPoints < 99) {
			$expire_time = 31 * 24 * 60 * 60;
		} elseif ($newPoints >= 100) {
			$expire_time = 0;
		}
	
		if ($expire_time >= 0) {
			if ($expire_time > 0) $expire_time += TIME;
			else $reasonID = 8;
			$db->query('UPDATE account_has_points SET last_update = '.$expire_time.' WHERE account_id = '.$var['offender']);
		
			$suspicion = 'Inappropriate In-Game Message';
			$db->query('REPLACE INTO account_is_closed ' .
					   '(account_id, reason_id, suspicion, expires) ' .
					   'VALUES('.$var['offender'].', '.$reasonID.', '.$db->escapeString($suspicion).', '.$expire_time.')');
		
			$db->query('INSERT INTO account_has_closing_history ' .
					   '(account_id, time, admin_id, action) ' .
					   'VALUES('.$var['offender'].', ' . TIME . ', '.SmrSession::$account_id.', \'Closed\')');
		
			$db->query('UPDATE player SET newbie_turns = 1 ' .
					   'WHERE account_id = '.$var['offender'].' AND ' .
							 'newbie_turns = 0 AND ' .
							 'land_on_planet = \'FALSE\'');
			$db->lockTable('active_session');
			$db->query('DELETE FROM active_session ' .
					   'WHERE account_id = '.$var['offender']);
			$db->unlock();
		}
	}
}
if (isset($_REQUEST['offendedReply'])) $offendedReply = $_REQUEST['offendedReply'];

if (isset($offendedReply) && $offendedReply != '') {
	//next message
	$message = $db->escape_string($offendedReply, false);
	$db->query('INSERT INTO message (account_id, game_id, message_type_id, message_text, sender_id, send_time) ' .
	                             'VALUES('.$var['offended'].', '.$game_id.', '.MSG_ADMIN.', '.$message.', 0, '.TIME.')');
	
	// give him the message icon
	$db->query('REPLACE INTO player_has_unread_messages (game_id, account_id, message_type_id) VALUES
				('.$game_id.', '.$var['offended'].', '.MSG_ADMIN.')');
	//do we have points?
	if ($_REQUEST['offendedBanPoints'])
	{
		$reasonID = 7;
		$db->query('SELECT * FROM account_has_points WHERE account_id = '.$var['offended']);
		if ($db->nextRecord())	{
	
			$currPoints = $db->getField('points');
			$newPoints = $currPoints + $_REQUEST['offendedBanPoints'];
			$db->query('UPDATE account_has_points SET points = '.$newPoints.', last_update = '.TIME.' WHERE account_id = '.$var['offended']);
	
		} else {
	
			$newPoints = $_REQUEST['offendedBanPoints'];
			$db->query('REPLACE INTO account_has_points (account_id, points, last_update) VALUES ('.$var['offended'].', '.$newPoints.', '.TIME.')');
	
		}
	
		// < 9 = warning
		if ($newPoints < 9) {
			$expire_time = -1;
		} elseif ($newPoints < 19) {
			$expire_time = 2 * 24 * 60 * 60;
		} elseif ($newPoints < 29) {
			$expire_time = 4 * 24 * 60 * 60;
		} elseif ($newPoints < 49) {
			$expire_time = 7 * 24 * 60 * 60;
		} elseif ($newPoints < 74) {
			$expire_time = 14 * 24 * 60 * 60;
		} elseif ($newPoints < 99) {
			$expire_time = 31 * 24 * 60 * 60;
		} elseif ($newPoints >= 100) {
			$expire_time = 0;
		}
	
		if ($expire_time >= 0) {
			if ($expire_time > 0) $expire_time += TIME;
			else $reasonID = 8;
			$db->query('UPDATE account_has_points SET last_update = '.$expire_time.' WHERE account_id = '.$var['offended']);
		
			$suspicion = 'Inappropriate In-Game Message';
			$db->query('REPLACE INTO account_is_closed ' .
					   '(account_id, reason_id, suspicion, expires) ' .
					   'VALUES('.$var['offended'].', '.$reasonID.', '.$db->escapeString($suspicion).', '.$expire_time.')');
		
			$db->query('INSERT INTO account_has_closing_history ' .
					   '(account_id, time, admin_id, action) ' .
					   'VALUES('.$var['offended'].', ' . TIME . ', '.SmrSession::$account_id.', \'Closed\')');
		
			$db->query('UPDATE player SET newbie_turns = 1 ' .
					   'WHERE account_id = '.$var['offended'].' AND ' .
							 'newbie_turns = 0 AND ' .
							 'land_on_planet = \'FALSE\'');
			$db->lockTable('active_session');
			$db->query('DELETE FROM active_session ' .
					   'WHERE account_id = '.$var['offended']);
			$db->unlock();
		}
	}
}
forward(create_container('skeleton.php', 'notify_view.php'));

?>