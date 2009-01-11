<?
$account_id = $_REQUEST['account_id'];
$game_id = $var['game_id'];
if (!empty($account_id) || $game_id == 20000) {

	$message = $_REQUEST['message'];
	$message = $db->escape_string($message, false);
	$expire = $_REQUEST['expire'];
	if ($expire > 0) $expire = ($expire * 3600) + time();
	$current_time = time();
	if ($game_id != 20000) {
		
		$db->query('INSERT INTO message (account_id, game_id, message_type_id, message_text, sender_id, send_time, expire_time)
					VALUES('.$account_id.', '.$game_id.', '.MSG_ADMIN.', '.$message.', 0, '.$current_time.', '.$expire.')');
	
		// give him the message icon
		$db->query('REPLACE INTO player_has_unread_messages (game_id, account_id, message_type_id) VALUES
					('.$game_id.', '.$account_id.', '.MSG_ADMIN.')');
					
	} else {
		
		//send to all players
		$db2 = new SmrMySqlDatabase();
		$db->query('SELECT * FROM player');
		while ($db->nextRecord()) {
			
			$db2->query('INSERT INTO message (account_id, game_id, message_type_id, message_text, sender_id, send_time, expire_time)' .
					'VALUES(' . $db->getField('account_id') . ', ' . $db->getField('game_id') . ', '.MSG_ADMIN.', '.$message.', 0, '.$current_time.', '.$expire.')');
			$db2->query('REPLACE INTO player_has_unread_messages (game_id, account_id, message_type_id) VALUES' .
					'(' . $db->getField('game_id') . ', ' . $db->getField('account_id') . ', '.MSG_ADMIN.')');
					
		}
		
	}
}

forward(create_container('skeleton.php', 'game_play.php'))

?>