<?php
$action = $_REQUEST['action'];
if ($action == "Marked Messages") {

    $message_id = $_REQUEST['message_id'];
    if (!isset($message_id))
        create_error("You must choose the messages you want to delete.");

    foreach ($message_id as $id) {
		if ($temp = @unserialize(base64_decode($id))) {
			$query = 'SELECT message_id FROM message 
						WHERE sender_id = ' . $temp[0] . '
						AND game_id = ' . $player->game_id . '
						AND send_time >= ' . $temp[1] . '
						AND send_time <= ' . $temp[2] . '
						AND account_id = ' . $player->account_id . '
						AND message_type_id = ' . MSG_SCOUT;
			$db->query($query);
			while ($db->next_record()) {
				$newId = $db->f("message_id");
				if ($message_id_list) $message_id_list .= ", ";
        		$message_id_list .= $newId;
			}
		} else {
	        if ($message_id_list) $message_id_list .= ", ";
	        $message_id_list .= $id;
	    }
    }
    $db->query("DELETE FROM message WHERE message_id IN ($message_id_list)");

} else {
    if ($var["folder_id"] == MSG_SCOUT) {
        $db->query("DELETE FROM message WHERE account_id = $player->account_id AND " .
                                            "message_type_id = $var[folder_id] AND " .
                                            "game_id = $player->game_id");
    } else {
        $db->query("DELETE FROM message WHERE account_id = ".SmrSession::$old_account_id." AND " .
                                           "game_id = ".SmrSession::$game_id." AND " .
                                           "message_type_id = " . $var["folder_id"] . " AND " .
                                           "msg_read = 'TRUE'");
    }
}

forward(create_container("skeleton.php", "message_view.php"));

?>