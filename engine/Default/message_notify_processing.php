<?php declare(strict_types=1);

$container = create_container('skeleton.php', 'message_view.php');
transfer('folder_id');

if (Request::get('action') == 'No') {
	forward($container);
}

if (empty($var['message_id'])) {
	create_error('Please click the small yellow icon to report a message!');
}

// get next id
$db->query('SELECT max(notify_id) FROM message_notify WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' ORDER BY notify_id DESC');
if ($db->nextRecord()) {
	$notify_id = $db->getInt('max(notify_id)') + 1;
} else {
	$notify_id = 1;
}

// get message form db
$db->query('SELECT player_id, sender_player_id, message_text
			FROM message
			WHERE message_id = ' . $var['message_id'] . ' AND receiver_delete = \'FALSE\'');
if (!$db->nextRecord()) {
	create_error('Could not find the message you selected!');
}

// insert
$db->query('INSERT INTO message_notify
			(notify_id, game_id, from_player_id, to_player_id, text, sent_time, notify_time)
			VALUES ('.$notify_id . ', ' . $db->escapeNumber($player->getGameID()) . ', ' . $db->getInt('sender_player_id') . ', ' . $db->getInt('player_id') . ', ' . $db->escapeString($db->getField('message_text')) . ', ' . $var['sent_time'] . ', ' . $var['notified_time'] . ')');

forward($container);
