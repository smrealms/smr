<?php declare(strict_types=1);

if ($var['folder_id'] == MSG_SENT) {
	$db->query('UPDATE message SET sender_delete = ' . $db->escapeBoolean(true) . '
				WHERE sender_player_id = ' . $db->escapeNumber($player->getPlayerID()) . '
					AND game_id = ' . $db->escapeNumber($player->getGameID()));
} else {
	$db->query('UPDATE message SET receiver_delete = ' . $db->escapeBoolean(true) . '
				WHERE player_id = ' . $db->escapeNumber($player->getPlayerID()) . '
					AND game_id = ' . $db->escapeNumber($player->getGameID()) . '
					AND message_type_id = ' . $db->escapeNumber($var['folder_id']) . '
					AND msg_read = ' . $db->escapeBoolean(true));
}

forward(create_container('skeleton.php', 'message_box.php'));
