<?

if (!isset($var['folder_id'])) {

	$template->assign('PageTopic','VIEW MESSAGES');

	include(get_file_loc('menue.inc'));
	$PHP_OUTPUT.=create_message_menue();

	$PHP_OUTPUT.=('<p>Please choose your Message folder!</p>');

	$PHP_OUTPUT.=('<p>');
	$PHP_OUTPUT.=('<table class="standard">');
	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<th>Folder</th>');
	$PHP_OUTPUT.=('<th>Messages</th>');
	$PHP_OUTPUT.=('<th>&nbsp;</th>');
	$PHP_OUTPUT.=('</tr>');

	$db2 = new SmrMySqlDatabase();
	
	include(get_file_loc('council.inc'));

	$db2->query('SELECT * FROM message WHERE account_id = '.$player->getAccountID().' AND message_type_id = '.MSG_POLITICAL.' AND game_id = '.$player->getGameID());
	if (onCouncil($player->getRaceID()) || $db2->getNumRows())
		$db->query('SELECT * FROM message_type ' .
				   'WHERE message_type_id < 8 ' .
				   'ORDER BY message_type_id');
	else
		$db->query('SELECT * FROM message_type ' .
					'WHERE message_type_id != 5 ' .
				   'ORDER BY message_type_id');

	while ($db->nextRecord()) {

		$message_type_id = $db->getField('message_type_id');
		$message_type_name = $db->getField('message_type_name');

		// do we have unread msges in that folder?
		$db2->query('SELECT * FROM message ' .
						'WHERE account_id = '.SmrSession::$account_id.' AND ' .
							  'game_id = '.SmrSession::$game_id.' AND ' .
							  'message_type_id = '.$message_type_id.' AND ' .
							  'msg_read = \'FALSE\'');
		$msg_read = $db2->getNumRows();

		// get number of msges
		$db2->query('SELECT count(message_id) as message_count FROM message ' .
						'WHERE account_id = '.SmrSession::$account_id.' AND ' .
							  'game_id = '.SmrSession::$game_id.' AND ' .
							  'message_type_id = '.$message_type_id);
		if ($db2->nextRecord())
			$message_count = $db2->getField('message_count');

		$PHP_OUTPUT.=('<tr>');
		$PHP_OUTPUT.=('<td');
		if ($msg_read) $PHP_OUTPUT.=(' style="font-weight:bold;"');
		$PHP_OUTPUT.=('>');
		$container = array();
		$container['url'] = 'skeleton.php';
		$container['body'] = 'message_view.php';
		$container['folder_id'] = $message_type_id;
		$PHP_OUTPUT.=create_link($container, $message_type_name);
		$PHP_OUTPUT.=('</td>');
		$PHP_OUTPUT.=('<td align="center" style="color:yellow;');
		if ($msg_read) $PHP_OUTPUT.=('font-weight:bold;');
		$PHP_OUTPUT.=('">'.$message_count.'</td>');
		$PHP_OUTPUT.=('<td');
		if ($msg_read) $PHP_OUTPUT.=(' style="font-weight:bold;"');
		$PHP_OUTPUT.=('>');
		$container = array();
		$container['url'] = 'message_delete_processing.php';
		$container['folder_id'] = $message_type_id;
		$PHP_OUTPUT.=create_link($container, 'Empty');
		$PHP_OUTPUT.=('</td>');
		$PHP_OUTPUT.=('</tr>');

	}

	$PHP_OUTPUT.=('</table>');
	$PHP_OUTPUT.=('</p><p>');
	
	$container = array();
	$container['url'] = 'skeleton.php';
	$container['body'] = 'message_blacklist.php';
	$PHP_OUTPUT.=create_link($container,'Manage Player Blacklist');
	
	$PHP_OUTPUT.= '</p>';

} else {
	
	$db->query('SELECT * FROM message ' .
						'WHERE account_id = '.SmrSession::$account_id.' AND ' .
							  'game_id = '.SmrSession::$game_id.' AND ' .
							  'message_type_id = ' . $var['folder_id'] . ' AND ' .
							  'msg_read = \'FALSE\'');
	$unread_messages = $db->getNumRows();
	// remove entry for this folder from unread msg table
	$player->setMessagesRead($var['folder_id']);

	$db->query('SELECT * FROM message_type WHERE message_type_id = ' . $var['folder_id']);
	if ($db->nextRecord())
		$template->assign('PageTopic','VIEW ' . $db->getField('message_type_name'));

	include(get_file_loc('menue.inc'));
	$PHP_OUTPUT.=create_message_menue();

	if ($var['folder_id'] == MSG_GLOBAL) {

		$PHP_OUTPUT.=create_echo_form(create_container('message_global_ignore.php', ''));
		$PHP_OUTPUT.=('<div align="center">Ignore global messages?&nbsp;&nbsp;');

		if ($player->isIgnoreGlobals())
			$PHP_OUTPUT.=create_submit_style('Yes', 'background-color:green;');
		else
			$PHP_OUTPUT.=create_submit('Yes');
		$PHP_OUTPUT.=('&nbsp;');
		if (!$player->isIgnoreGlobals())
			$PHP_OUTPUT.=create_submit_style('No', 'background-color:green;');
		else
			$PHP_OUTPUT.=create_submit('No');
		$PHP_OUTPUT.=('</div></form>');

	}

	$PHP_OUTPUT.=('<br />');
	$container = array();
	$container['url'] = 'message_delete_processing.php';
	transfer('folder_id');

	$PHP_OUTPUT.=create_echo_form($container);
	$PHP_OUTPUT.=create_submit('Delete');
	$PHP_OUTPUT.=('&nbsp;');
	$PHP_OUTPUT.=('<select name="action" size="1" id="InputFields">');
	$PHP_OUTPUT.=('<option>Marked Messages</option>');
	$PHP_OUTPUT.=('<option>All Messages</option>');
	$PHP_OUTPUT.=('</select>');
	$db->query('SELECT * FROM message ' .
						'WHERE account_id = '.$player->getAccountID().' AND ' .
							  'game_id = '.$player->getGameID().' AND ' .
							  'message_type_id = ' . $var['folder_id'] .
						' ORDER BY send_time DESC');
	$message_count = $db->getNumRows();

	$PHP_OUTPUT.=('<p>You have <span style="color:yellow;">'.$message_count.'</span> message');
	if ($message_count != 1)
		$PHP_OUTPUT.=('s');
	$PHP_OUTPUT.=('.</p>');
	$PHP_OUTPUT.=('<table width="100%" class="standard">');
	if ($var['folder_id'] == MSG_SCOUT && !isset($var['show_all'])) {
		$dispContainer = array();
		$dispContainer['url'] = 'skeleton.php';
		$dispContainer['body'] = 'message_view.php';
		$dispContainer['folder_id'] = MSG_SCOUT;
		$dispContainer['show_all'] = TRUE;
		if ($unread_messages > 25 || $message_count - $unread_messages > 25) {
			$PHP_OUTPUT.=create_button($dispContainer, 'Show all Messages');
			$PHP_OUTPUT.=('<br />');
		}
		if ($unread_messages > 25) {
			//here we group new messages
			$query = 'SELECT alignment, player_id, sender_id, player_name AS sender, count( message_id ) AS number, min( send_time ) as first, max( send_time) as last 
					FROM message, player 
					WHERE player.account_id = message.sender_id 
					AND message.account_id = ' . $player->getAccountID() . '
					AND message.game_id = ' . $player->getGameID()  . '
					AND player.game_id = ' . $player->getGameID() . '
					AND message_type_id = ' . $var['folder_id'] . '
					AND msg_read = \'FALSE\' 
					GROUP BY sender_id 
					ORDER BY send_time DESC';
			$db->query($query);
			while ($db->nextRecord()) {
				//display grouped stuff (allow for deletion)
				$playerName = get_colored_text($db->getField('alignment'), stripslashes($db->getField('sender')) . ' (' . $db->getField('player_id') . ')');
				$message = 'Your forces have spotted ' . $playerName . ' passing your forces ' . $db->getField('number') . ' times.';
				$PHP_OUTPUT.=displayGrouped($playerName, $db->getField('player_id'), $db->getField('sender_id'), $message, $db->getField('first'), $db->getField('last'), TRUE);
			}
		} else {
			//not enough to group, display separatly
			$query = 'SELECT message_id, sender_id, message_text, send_time, msg_read
					FROM message
					WHERE account_id = ' . $player->getAccountID() . '
					AND game_id = ' . $player->getGameID() . '
					AND message_type_id = ' . $var['folder_id'] . '
					AND msg_read = \'FALSE\' 
					ORDER BY send_time DESC';
			$db->query($query);
			while ($db->nextRecord())
				$PHP_OUTPUT.=displayMessage($db->getField('message_id'), $db->getField('sender_id'), stripslashes($db->getField('message_text')), $db->getField('send_time'), $db->getField('msg_read'), $var['folder_id']);
		}
		if ($message_count - $unread_messages > 25) {
			$query = 'SELECT alignment, player_id, sender_id, player_name AS sender, count( message_id ) AS number, min( send_time ) as first, max( send_time) as last 
					FROM message, player 
					WHERE player.account_id = message.sender_id 
					AND message.account_id = ' . $player->getAccountID()  . '
					AND message.game_id = ' . $player->getGameID() . '
					AND player.game_id = ' . $player->getGameID() . '
					AND message_type_id = ' . $var['folder_id'] . '
					AND msg_read = \'TRUE\' 
					GROUP BY sender_id 
					ORDER BY send_time DESC';
			$db->query($query);
			while ($db->nextRecord()) {
				$playerName = get_colored_text($db->getField('alignment'), stripslashes($db->getField('sender')) . ' (' . $db->getField('player_id') . ')');
				$message = 'Your forces have spotted ' . $playerName . ' passing your forces ' . $db->getField('number') . ' times.';
				$PHP_OUTPUT.=displayGrouped($playerName, $db->getField('player_id'), $db->getField('sender_id'), $message, $db->getField('first'), $db->getField('last'), FALSE);
			}
		} else {
			$query = 'SELECT * FROM message 
					WHERE account_id = ' . $player->getAccountID() . ' AND
					game_id = ' . $player->getGameID() . '
					AND message_type_id = ' . $var['folder_id'] . '
					AND msg_read = \'TRUE\'
					ORDER BY send_time DESC';
			$db->query($query);
			while ($db->nextRecord())
				$PHP_OUTPUT.=displayMessage($db->getField('message_id'), $db->getField('sender_id'), stripslashes($db->getField('message_text')), $db->getField('send_time'), $db->getField('msg_read'),$var['folder_id']);
		}
		$db->query('UPDATE message SET msg_read = \'TRUE\' WHERE message_type_id = '.MSG_SCOUT.' AND game_id = '.$player->getGameID().' AND account_id = '.$player->getAccountID());
	} else {
		while ($db->nextRecord()) {
			$message_id = $db->getField('message_id');
			$sender_id = $db->getField('sender_id');
			$message_text = stripslashes($db->getField('message_text'));
			$send_time = $db->getField('send_time');
			$msg_read = $db->getField('msg_read');
			$PHP_OUTPUT.=displayMessage($message_id, $sender_id, $message_text, $send_time, $msg_read, $var['folder_id']);
		}
		$db->query('UPDATE message SET msg_read = \'TRUE\' WHERE message_type_id = '.$var['folder_id'].' AND game_id = '.$player->getGameID().' AND account_id = '.$player->getAccountID());
	}
	$PHP_OUTPUT.=('</table></form>');

}
function displayGrouped($playerName, $player_id, $sender_id, $message_text, $first, $last, $star) {
	$array = array($sender_id, $first, $last);

	$return= ('<tr><td width="10"><input type="checkbox" name="message_id[]" value="' . base64_encode(serialize($array)) . '">');
	if ($star) $return.= ('*');
	$return.= ('</td>');
	$return.= ('<td nowrap="nowrap" width="100%">From: ');
	$container = array();
	$container['url']		= 'skeleton.php';
	$container['body']		= 'trader_search_result.php';
	$container['player_id'] = $player_id;
	$return.= create_link($container, $playerName);
	$return.= ('</td>');
	$return.= ('<td nowrap="nowrap" colspan="2">Date: ' . date(DATE_FULL_SHORT, $first) . ' - ' . date(DATE_FULL_SHORT, $last) . '</td></tr>');
	$return.= ('<tr>');
	$return.= ('<td colspan="4">');
	//insert link to expand them.
	$return.= ($message_text);
	$return.= ('</td>');
	$return.= ('</tr>');
	return $return;
}
function displayMessage($message_id, $sender_id, $message_text, $send_time, $msg_read, $type) {
	global $player, $account;
	
	$replace = explode('!', $message_text);
	foreach ($replace as $key => $timea)
	{
		if ($timea != '' && ($final = strtotime($timea)) !== false) //WARNING: Expects PHP 5.1.0 or later
		{
			$final += $account->offset * 3600;
			$message_text = str_replace('!$timea!', date(DATE_FULL_SHORT, $final), $message_text);
		}
	}
	$replace = explode('?', $message_text);
	foreach ($replace as $key => $timea)
	{
		if ($sender_id > 0 && $timea != '' && ($final = strtotime($timea)) !== false) //WARNING: Expects PHP 5.1.0 or later
		{	
			$send_acc =& SmrAccount::getAccount($sender_id);
			$final += ($account->offset * 3600 - $send_acc->offset * 3600);
			$message_text = str_replace('?$timea?', date(DATE_FULL_SHORT, $final), $message_text);
		}
	}
	if (!empty($sender_id) && $sender_id!=ACCOUNT_ID_PORT&&$sender_id!=ACCOUNT_ID_ADMIN&&$sender_id!=ACCOUNT_ID_PLANET)
		$sender =& SmrPlayer::getPlayer($sender_id, $player->getGameID());
	$return= ('<tr>');
	$return.= ('<td width="10"><input type="checkbox" name="message_id[]" value="'.$message_id.'">');
	// remember id for marking as read
	if ($msg_read == 'FALSE') $return.= ('*');
	$return.= ('</td>');
	$return.= ('<td nowrap="nowrap" width="100%">From: ');
	if($sender_id==ACCOUNT_ID_PORT)
	{
		$return.= '<span class="yellow">Port Defenses</span>';
	}
	else if($sender_id==ACCOUNT_ID_ADMIN)
	{
		$return.= '<span style="font:small-caps bold;color:blue;">Administrator</span>';
	}
	else if($sender_id==ACCOUNT_ID_PLANET)
	{
		$return.= '<span class="yellow">Planetary Defenses</span>';
	}
	else if (is_object($sender))
	{
		$container = array();
		$container['url']		= 'skeleton.php';
		$container['body']		= 'trader_search_result.php';
		$container['player_id'] = $sender->getPlayerID();
		$return.= create_link($container, $sender->getDisplayName());
	}
	else
	{
		if ($type == 7)
			$return.= ('<span style="font:small-caps bold;color:blue;">Administrator</span>');
		elseif ($type == 6)
		{
			$return.= ('<span class="green">');
			$return.= ('Alliance Ambassador');
			$return.= ('</span>');
		}
		elseif ($type == 2) $return.= ('<span class="yellow">Port Defenses</span>');
		else $return.= ('Unknown');
	}
	$return.= ('</td>');
	$return.= ('<td nowrap="nowrap"'.(!is_object($sender)?' colspan="3"':'').'>Date: ' . date(DATE_FULL_SHORT, $send_time) . '</td>');
	$return.= ('<td>');
	$container = array();
	$container['url'] = 'skeleton.php';
	$container['body'] = 'message_notify_confirm.php';
	$container['message_id'] = $message_id;
	$container['sent_time'] = $send_time;
	$container['notified_time'] = TIME;
	$return.= create_link($container, '<img src="images/notify.gif" border="0" align="right"title="Report this message to an admin">');
	$return.= ('</td>');
	if (is_object($sender))
	{
		$return.= ('<td>');
		$container = array();
		$container['url'] = 'skeleton.php';
		$container['body'] = 'message_blacklist_add.php';
		$container['account_id'] = $sender_id;
		$return.= create_link($container, 'Blacklist Player');
		$return.= ('</td>');	
		$return.= ('<td>');
		$container = array();
		$container['url']		= 'skeleton.php';
		$container['body']		= 'message_send.php';
		$container['receiver']	= $sender->getAccountID();
		$return.=create_link($container, 'Reply');
		$return.= ('</td>');
	}
	
	$return.= ('</tr>');
	$return.= ('<tr>');
	$return.= ('<td colspan="6">'.bbifyMessage($message_text).'</td>');
	$return.= ('</tr>');
	return $return;
}
?>