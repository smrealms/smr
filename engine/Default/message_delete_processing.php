<?php
$action = $_REQUEST['action'];
if ($action == 'Marked Messages')
{
	$message_id = $_REQUEST['message_id'];
	if (!isset($message_id))
		create_error('You must choose the messages you want to delete.');

	foreach ($message_id as $id)
	{
		if ($temp = @unserialize(base64_decode($id)))
		{
			$query = 'SELECT message_id FROM message 
						WHERE sender_id = ' . $temp[0] . '
						AND game_id = ' . $player->getGameID() . '
						AND send_time >= ' . $temp[1] . '
						AND send_time <= ' . $temp[2] . '
						AND account_id = ' . $player->getAccountID() . '
						AND message_type_id = ' . MSG_SCOUT.' AND reciever_delete = \'FALSE\'';
			$db->query($query);
			while ($db->nextRecord())
			{
				$newId = $db->getField('message_id');
				if ($message_id_list) $message_id_list .= ', ';
				$message_id_list .= $newId;
			}
		}
		else
		{
			if ($message_id_list) $message_id_list .= ', ';
			$message_id_list .= $id;
		}
	}
	if($var['folder_id']==MSG_SENT)
		$db->query('UPDATE message SET sender_delete = \'TRUE\' WHERE message_id IN ('.$message_id_list.')');
	else
		$db->query('UPDATE message SET reciever_delete = \'TRUE\' WHERE message_id IN ('.$message_id_list.')');
}
else
{
	if ($var['folder_id'] == MSG_SCOUT)
	{
		$db->query('UPDATE message SET reciever_delete = \'TRUE\' WHERE account_id = '.$player->getAccountID().' AND ' .
											'message_type_id = '.$var['folder_id'].' AND ' .
											'game_id = '.$player->getGameID());
	}
	else if ($var['folder_id'] == MSG_SENT)
	{
		$db->query('UPDATE message SET sender_delete = \'TRUE\' WHERE sender_id = '.$player->getAccountID().' AND ' .
											'game_id = '.$player->getGameID());
	}
	else
	{
		$db->query('UPDATE message SET reciever_delete = \'TRUE\' WHERE account_id = '.SmrSession::$account_id.' AND ' .
										   'game_id = '.$player->getGameID().' AND ' .
										   'message_type_id = ' . $var['folder_id'] . ' AND ' .
										   'msg_read = \'TRUE\'');
	}
}

forward(create_container('skeleton.php', 'message_view.php'));

?>