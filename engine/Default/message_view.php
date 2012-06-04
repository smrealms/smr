<?php

require_once(get_file_loc('menue.inc'));
create_message_menue();

if (!isset($var['folder_id']))
{
	$template->assign('PageTopic','View Messages');


	$db2 = new SmrMySqlDatabase();
	
	include(get_file_loc('council.inc'));

	$db2->query('SELECT * FROM message WHERE account_id = '.$player->getAccountID().' AND message_type_id = '.MSG_POLITICAL.' AND game_id = '.$player->getGameID().' AND reciever_delete = \'FALSE\' AND reciever_delete = \'FALSE\'');
	if (onCouncil($player->getRaceID()) || $db2->getNumRows())
		$db->query('SELECT * FROM message_type ' .
				   'WHERE message_type_id < 8 ' .
				   'ORDER BY message_type_id');
	else
		$db->query('SELECT * FROM message_type ' .
					'WHERE message_type_id != 5 ' .
				   'ORDER BY message_type_id');
	$messageBoxes = array();
	while ($db->nextRecord())
	{
		$message_type_id = $db->getField('message_type_id');
		$messageBox['Name'] = $db->getField('message_type_name');

		// do we have unread msges in that folder?
		$db2->query('SELECT message_id FROM message ' .
						'WHERE account_id = '.SmrSession::$account_id.' AND ' .
							  'game_id = '.SmrSession::$game_id.' AND ' .
							  'message_type_id = '.$message_type_id.' AND ' .
							  'msg_read = \'FALSE\' AND ' .
							  'reciever_delete = \'FALSE\' LIMIT 1'
					);
		$messageBox['HasUnread'] = $db2->getNumRows() != 0;

		$messageBox['MessageCount'] = 0;
		// get number of msges
		$db2->query('SELECT count(message_id) as message_count FROM message ' .
						'WHERE account_id = '.SmrSession::$account_id.' AND ' .
							  'game_id = '.SmrSession::$game_id.' AND ' .
							  'message_type_id = '.$message_type_id.' AND reciever_delete = \'FALSE\'');
		if ($db2->nextRecord())
			$messageBox['MessageCount'] = $db2->getField('message_count');

		$container = create_container('skeleton.php','message_view.php');
		$container['folder_id'] = $message_type_id;
		$messageBox['ViewHref'] = SmrSession::get_new_href($container);
		
		$container = create_container('message_delete_processing.php');
		$container['folder_id'] = $message_type_id;
		$messageBox['DeleteHref'] = SmrSession::get_new_href($container);
		$messageBoxes[] = $messageBox;
	}
	
	$messageBox = array();
	$messageBox['MessageCount'] = 0;
	$db->query('SELECT count(message_id) as count FROM message ' .
						'WHERE sender_id = '.SmrSession::$account_id.' AND ' .
							  'game_id = '.SmrSession::$game_id.' AND ' .
							  'message_type_id = ' . MSG_PLAYER.' AND ' .
							  'sender_delete = \'FALSE\'');
	if ($db->nextRecord())
		$messageBox['MessageCount'] = $db->getField('count');
	$messageBox['Name'] = 'Sent Messages';
	$messageBox['HasUnread'] = false;
	$container = create_container('skeleton.php','message_view.php');
	$container['folder_id'] = MSG_SENT;
	$messageBox['ViewHref'] = SmrSession::get_new_href($container);
	
	$container = create_container('message_delete_processing.php');
	$container['folder_id'] = MSG_SENT;
	$messageBox['DeleteHref'] = SmrSession::get_new_href($container);
	$messageBoxes[] = $messageBox;
			
	$template->assignByRef('MessageBoxes',$messageBoxes);
}
else
{
	$db->query('SELECT count(*) as count FROM message ' .
						'WHERE account_id = '.SmrSession::$account_id.' AND ' .
							  'game_id = '.SmrSession::$game_id.' AND ' .
							  'message_type_id = ' . $var['folder_id'] . ' AND ' .
							  'msg_read = \'FALSE\' AND reciever_delete = \'FALSE\'');
	$db->nextRecord();
	$messageBox['UnreadMessages'] = $db->getField('count');
	$messageBox['Type'] = $var['folder_id'];
	// remove entry for this folder from unread msg table
	$player->setMessagesRead($messageBox['Type']);

	if($var['folder_id'] == MSG_SENT)
		$messageBox['Name'] = 'Sent Messages';
	else
	{
		$db->query('SELECT * FROM message_type WHERE message_type_id = ' . $var['folder_id']);
		if ($db->nextRecord())
			$messageBox['Name'] = $db->getField('message_type_name');
	}
	$template->assign('PageTopic','Viewing ' . $messageBox['Name']);

	if ($messageBox['Type'] == MSG_GLOBAL)
	{
		$template->assign('IgnoreGlobalsFormHref', SmrSession::get_new_href(create_container('message_global_ignore.php')));
	}

	$container = create_container('message_delete_processing.php');
	transfer('folder_id');
	$messageBox['DeleteFormHref'] = SmrSession::get_new_href($container);
	
	if($var['folder_id'] == MSG_SENT)
		$db->query('SELECT * FROM message ' .
						'WHERE sender_id = '.$player->getAccountID().' AND ' .
							  'game_id = '.$player->getGameID().' AND ' .
							  'message_type_id = ' . MSG_PLAYER.' AND ' .
							  'sender_delete = \'FALSE\'' .
						' ORDER BY send_time DESC');
	else
		$db->query('SELECT * FROM message ' .
						'WHERE account_id = '.$player->getAccountID().' AND ' .
							  'game_id = '.$player->getGameID().' AND ' .
							  'message_type_id = ' . $var['folder_id'].' AND reciever_delete = \'FALSE\'' .
						' ORDER BY send_time DESC');

	$messageBox['NumberMessages'] = $db->getNumRows();
	$messageBox['Messages'] = array();

	if ($var['folder_id'] == MSG_SCOUT && !isset($var['show_all']))
	{
		// get rid of all old scout messages (>48h)
		$db2 = new SmrMySqlDatabase();
		$db2->query('DELETE FROM message WHERE expire_time < '.TIME.' AND message_type_id = '.MSG_SCOUT);
		
		if ($messageBox['UnreadMessages'] > MESSAGE_SCOUT_GROUP_LIMIT || $messageBox['NumberMessages'] - $messageBox['UnreadMessages'] > MESSAGE_SCOUT_GROUP_LIMIT)
		{
			$dispContainer = create_container('skeleton.php','message_view.php');
			$dispContainer['folder_id'] = MSG_SCOUT;
			$dispContainer['show_all'] = true;
			$messageBox['ShowAllHref'] = SmrSession::get_new_href($dispContainer);
		}
		displayScouts($messageBox,$player,false,$messageBox['UnreadMessages'] > MESSAGE_SCOUT_GROUP_LIMIT);
		displayScouts($messageBox,$player,true,$messageBox['NumberMessages'] - $messageBox['UnreadMessages'] > MESSAGE_SCOUT_GROUP_LIMIT);
	}
	else
	{
		while ($db->nextRecord())
		{
			displayMessage($messageBox,$db->getField('message_id'), $db->getField('account_id'), $db->getField('sender_id'), $db->getField('message_text'), $db->getField('send_time'), $db->getField('msg_read'), $var['folder_id'], $var['folder_id']==0);
		}
	}
	if(!USING_AJAX)
		$db->query('UPDATE message SET msg_read = \'TRUE\' WHERE message_type_id = '.$var['folder_id'].' AND game_id = '.$player->getGameID().' AND account_id = '.$player->getAccountID());
	$template->assignByRef('MessageBox',$messageBox);
}

function displayScouts(&$messageBox, &$player, $read, $group)
{
	global $db;
	if ($group)
	{
		//here we group new messages
		$query = 'SELECT alignment, player_id, sender_id, player_name AS sender, count( message_id ) AS number, min( send_time ) as first, max( send_time) as last, msg_read
				FROM message, player 
				WHERE player.account_id = message.sender_id 
				AND message.account_id = ' . $player->getAccountID() . '
				AND message.game_id = ' . $player->getGameID()  . '
				AND player.game_id = ' . $player->getGameID() . '
				AND message_type_id = ' . MSG_SCOUT . '
				AND reciever_delete = \'FALSE\'
				AND msg_read = '.$db->escapeBoolean($read).'
				GROUP BY sender_id, msg_read
				ORDER BY send_time DESC';

		$db->query($query);
		while ($db->nextRecord())
		{
			//display grouped stuff (allow for deletion)
			$playerName = get_colored_text($db->getField('alignment'), stripslashes($db->getField('sender')) . ' (' . $db->getField('player_id') . ')');
			$message = 'Your forces have spotted ' . $playerName . ' passing your forces ' . $db->getField('number') . ' times.';
			displayGrouped($messageBox,$playerName, $db->getField('player_id'), $db->getField('sender_id'), $message, $db->getField('first'), $db->getField('last'), $db->getField('msg_read') == 'FALSE');
		}
	}
	else
	{
		//not enough to group, display separately
		$query = 'SELECT message_id, account_id, sender_id, message_text, send_time, msg_read
				FROM message
				WHERE account_id = ' . $player->getAccountID() . '
				AND game_id = ' . $player->getGameID() . '
				AND message_type_id = ' . MSG_SCOUT . '
				AND reciever_delete = \'FALSE\'
				AND msg_read = '.$db->escapeBoolean($read).'
				ORDER BY send_time DESC';
		$db->query($query);
		while ($db->nextRecord())
			displayMessage($messageBox,$db->getField('message_id'), $db->getField('account_id'), $db->getField('sender_id'), stripslashes($db->getField('message_text')), $db->getField('send_time'), $db->getField('msg_read'), MSG_SCOUT);
	}
}

function displayGrouped(&$messageBox,$playerName, $player_id, $sender_id, $message_text, $first, $last, $star)
{
	$array = array($sender_id, $first, $last);

	$message = array();	
	$message['ID'] = base64_encode(serialize($array));
	$message['Unread'] = $star;
	$container = create_container('skeleton.php','trader_search_result.php');
	$container['player_id'] = $player_id;
	$message['SenderDisplayName'] = create_link($container, $playerName);
	$message['FirstSendTime'] = $first;
	$message['LastSendTime'] = $last;
	$message['Text'] = $message_text;
	$messageBox['GroupedMessages'][] = $message;
}
function displayMessage(&$messageBox,$message_id, $reciever_id, $sender_id, $message_text, $send_time, $msg_read, $type,$sentMessage = false)
{
	global $player, $account;
	
	$message = array();
	
	$replace = explode('!', $message_text);
	foreach ($replace as $key => $timea)
	{
		if ($timea != '' && ($final = strtotime($timea)) !== false) //WARNING: Expects PHP 5.1.0 or later
		{
			$final += $account->offset * 3600;
			$message_text = str_replace('!$timea!', date(DATE_FULL_SHORT, $final), $message_text);
		}
	}
	$sender = false;
	if (!empty($sender_id) && $sender_id!=ACCOUNT_ID_PORT&&$sender_id!=ACCOUNT_ID_ADMIN&&$sender_id!=ACCOUNT_ID_PLANET)
		$sender =& SmrPlayer::getPlayer($sender_id, $player->getGameID());
	
	$senderName = '';
	if($sender_id==ACCOUNT_ID_PORT)
	{
		$senderName.= '<span class="yellow">Port Defenses</span>';
	}
	else if($sender_id==ACCOUNT_ID_ADMIN)
	{
		$senderName.= '<span style="font:small-caps bold;color:blue;">Administrator</span>';
	}
	else if($sender_id==ACCOUNT_ID_PLANET)
	{
		$senderName.= '<span class="yellow">Planetary Defenses</span>';
	}
	else if (is_object($sender))
	{
		$replace = explode('?', $message_text);
		foreach ($replace as $key => $timea)
		{
			if ($sender_id > 0 && $timea != '' && ($final = strtotime($timea)) !== false) //WARNING: Expects PHP 5.1.0 or later
			{	
				$send_acc =& $sender->getAccount();
				$final += ($account->offset * 3600 - $send_acc->offset * 3600);
				$message_text = str_replace('?$timea?', date(DATE_FULL_SHORT, $final), $message_text);
			}
		}
		$container = create_container('skeleton.php','trader_search_result.php');
		$container['player_id'] = $sender->getPlayerID();
		$senderName.= create_link($container, $sender->getDisplayName());
	}
	else
	{
		if ($type == 7)
			$senderName.= ('<span style="font:small-caps bold;color:blue;">Administrator</span>');
		elseif ($type == 6)
		{
			$senderName.= ('<span class="green">Alliance Ambassador</span>');
		}
		elseif ($type == 2) $senderName.= ('<span class="yellow">Port Defenses</span>');
		else $senderName.= ('Unknown');
	}
	$container = create_container('skeleton.php','message_notify_confirm.php');
	$container['message_id'] = $message_id;
	$container['sent_time'] = $send_time;
	$container['notified_time'] = TIME;
	$message['ReportHref'] = SmrSession::get_new_href($container);
	if (is_object($sender))
	{
		$container = create_container('skeleton.php','message_blacklist_add.php');
		$container['account_id'] = $sender_id;
		$message['BlacklistHref'] = SmrSession::get_new_href($container);
		
		$container = create_container('skeleton.php','message_send.php');
		$container['receiver']	= $sender->getAccountID();
		$message['ReplyHref'] = SmrSession::get_new_href($container);
	}
	
	$message['ID'] = $message_id;
	$message['Text'] = $message_text;
	$message['SenderDisplayName'] = $senderName;
	if(is_object($sender))
		$message['Sender'] =& $sender;
		
	$reciever =& SmrPlayer::getPlayer($reciever_id, $player->getGameID());
	if($sentMessage && is_object($reciever))
	{
		$container = create_container('skeleton.php','trader_search_result.php');
		$container['player_id'] = $reciever->getPlayerID();
		$message['RecieverDisplayName'] = create_link($container, $reciever->getDisplayName());
	}
	
	$message['Unread'] = $msg_read == 'FALSE';
	$message['SendTime'] = $send_time;
	$messageBox['Messages'][] =& $message;
}
?>