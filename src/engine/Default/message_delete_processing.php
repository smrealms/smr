<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();

// If not deleting marked messages, we are deleting entire folders
if (Smr\Request::get('action') == 'All Messages') {
	$container = Page::create('message_box_delete_processing.php');
	$container->addVar('folder_id');
	$container->go();
} else {
	if (!Smr\Request::has('message_id')) {
		create_error('You must choose the messages you want to delete.');
	}

	$message_id_list = [];
	$db = Smr\Database::getInstance();
	foreach (Smr\Request::getArray('message_id') as $id) {
		if ($temp = @unserialize(base64_decode($id))) {
			$dbResult = $db->read('SELECT message_id FROM message
						WHERE sender_id = ' . $db->escapeNumber($temp[0]) . '
						AND game_id = ' . $db->escapeNumber($player->getGameID()) . '
						AND send_time >= ' . $db->escapeNumber($temp[1]) . '
						AND send_time <= ' . $db->escapeNumber($temp[2]) . '
						AND account_id = ' . $db->escapeNumber($player->getAccountID()) . '
						AND message_type_id = ' . $db->escapeNumber(MSG_SCOUT) . ' AND receiver_delete = ' . $db->escapeBoolean(false));
			foreach ($dbResult->records() as $dbRecord) {
				$message_id_list[] = $dbRecord->getInt('message_id');
			}
		} else {
			$message_id_list[] = $id;
		}
	}
	if ($var['folder_id'] == MSG_SENT) {
		$db->write('UPDATE message SET sender_delete = ' . $db->escapeBoolean(true) . ' WHERE message_id IN (' . $db->escapeArray($message_id_list) . ')');
	} else {
		$db->write('UPDATE message SET receiver_delete = ' . $db->escapeBoolean(true) . ' WHERE message_id IN (' . $db->escapeArray($message_id_list) . ')');
	}
}

$container = Page::create('skeleton.php', 'message_view.php');
$container->addVar('folder_id');
$container->go();
