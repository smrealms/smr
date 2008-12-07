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
					VALUES($account_id, $game_id, $ADMINMSG, $message, 0, $current_time, $expire)');
	
		// give him the message icon
		$db->query('REPLACE INTO player_has_unread_messages (game_id, account_id, message_type_id) VALUES
					($game_id, $account_id, $ADMINMSG)');
					
	} else {
		
		//send to all players
		$db2 = new SMR_DB();
		$db->query('SELECT * FROM player');
		while ($db->next_record()) {
			
			$db2->query('INSERT INTO message (account_id, game_id, message_type_id, message_text, sender_id, send_time, expire_time)' .
					'VALUES(' . $db->f('account_id') . ', ' . $db->f('game_id') . ', '.$ADMINMSG.', '.$message.', 0, '.$current_time.', '.$expire.')');
			$db2->query('REPLACE INTO player_has_unread_messages (game_id, account_id, message_type_id) VALUES' .
					'(' . $db->f('game_id') . ', ' . $db->f('account_id') . ', '.$ADMINMSG.')');
					
		}
		
	}
}

forward(create_container('skeleton.php', 'game_play.php'))

?>