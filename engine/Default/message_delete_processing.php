<?php declare(strict_types=1);

// If not deleting marked messages, we are deleting entire folders
if (Request::get('action') == 'All Messages') {
	$container = create_container('message_box_delete_processing.php');
	transfer('folder_id');
	forward($container);
} else {
	if (!Request::has('message_id')) {
		create_error('You must choose the messages you want to delete.');
	}

	$message_id_list = array();
	foreach (Request::getArray('message_id') as $id) {
		if ($temp = @unserialize(base64_decode($id))) {
			$db->query('SELECT message_id FROM message
						WHERE sender_player_id = ' . $db->escapeNumber($temp[0]) . '
						AND game_id = ' . $db->escapeNumber($player->getGameID()) . '
						AND send_time >= ' . $db->escapeNumber($temp[1]) . '
						AND send_time <= ' . $db->escapeNumber($temp[2]) . '
						AND player_id = ' . $db->escapeNumber($player->getPlayerID()) . '
						AND message_type_id = ' . $db->escapeNumber(MSG_SCOUT) . ' AND receiver_delete = ' . $db->escapeBoolean(false));
			while ($db->nextRecord()) {
				$message_id_list[] = $db->getInt('message_id');
			}
		} else {
			$message_id_list[] = $id;
		}
	}
	if ($var['folder_id'] == MSG_SENT) {
		$db->query('UPDATE message SET sender_delete = ' . $db->escapeBoolean(true) . ' WHERE message_id IN (' . $db->escapeArray($message_id_list) . ')');
	} else {
		$db->query('UPDATE message SET receiver_delete = ' . $db->escapeBoolean(true) . ' WHERE message_id IN (' . $db->escapeArray($message_id_list) . ')');
	}
}

$container = create_container('skeleton.php', 'message_view.php');
transfer('folder_id');
forward($container);
