<?

if (isset($_REQUEST['message']) && $_REQUEST['message'] != '')
{
	SmrPlayer::sendMessageFromAdmin($var['game_id'], $var['sender_id'], $_REQUEST['message']);

	//do we have points?
	if ($_REQUEST['BanPoints'])
	{
		$reasonID = 7;
		$db->query('SELECT * FROM account_has_points WHERE account_id = '.$var['sender_id']);
		if ($db->nextRecord())	{
	
			$currPoints = $db->getField('points');
			$newPoints = $currPoints + $_REQUEST['BanPoints'];
			$db->query('UPDATE account_has_points SET points = '.$newPoints.', last_update = '.TIME.' WHERE account_id = '.$var['sender_id']);
	
		} else {
	
			$newPoints = $_REQUEST['BanPoints'];
			$db->query('REPLACE INTO account_has_points (account_id, points, last_update) VALUES ('.$var['sender_id'].', '.$newPoints.', '.TIME.')');
	
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
			$db->query('UPDATE account_has_points SET last_update = '.$expire_time.' WHERE account_id = '.$var['sender_id']);
		
			$suspicion = 'Inappropriate In-Game Message';
			$db->query('REPLACE INTO account_is_closed ' .
					   '(account_id, reason_id, suspicion, expires) ' .
					   'VALUES('.$var['sender_id'].', '.$reasonID.', '.$db->escapeString($suspicion).', '.$expire_time.')');
		
			$db->query('INSERT INTO account_has_closing_history ' .
					   '(account_id, time, admin_id, action) ' .
					   'VALUES('.$var['sender_id'].', ' . TIME . ', '.SmrSession::$account_id.', \'Closed\')');
		
			$db->query('UPDATE player SET newbie_turns = 1 ' .
					   'WHERE account_id = '.$var['sender_id'].' AND ' .
							 'newbie_turns = 0 AND ' .
							 'land_on_planet = \'FALSE\'');
			$db->lockTable('active_session');
			$db->query('DELETE FROM active_session ' .
					   'WHERE account_id = '.$var['sender_id']);
			$db->unlock();
		}
	}
}
forward(create_container('skeleton.php', 'box_view.php'));

?>