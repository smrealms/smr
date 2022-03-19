<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();
$db = Smr\Database::getInstance();

// If not deleting marked messages, we are deleting entire folders
if (Smr\Request::get('action') == 'All Messages') {
	$container = Page::create('message_box_delete_processing.php');
	$container->addVar('folder_id');
	$container->go();
}

if (!Smr\Request::has('message_id') && !Smr\Request::has('group_id')) {
	create_error('You must choose the messages you want to delete.');
}

// Delete any individually selected messages
$message_id_list = Smr\Request::getIntArray('message_id', []);
if (!empty($message_id_list)) {
	if ($var['folder_id'] == MSG_SENT) {
		$db->write('UPDATE message SET sender_delete = ' . $db->escapeBoolean(true) . ' WHERE message_id IN (' . $db->escapeArray($message_id_list) . ')');
	} else {
		$db->write('UPDATE message SET receiver_delete = ' . $db->escapeBoolean(true) . ' WHERE message_id IN (' . $db->escapeArray($message_id_list) . ')');
	}
}

// Delete any scout message groups
foreach (Smr\Request::getArray('group_id', []) as $groupID) {
	[$senderID, $minTime, $maxTime] = unserialize(base64_decode($groupID));
	$db->write('UPDATE message SET receiver_delete = ' . $db->escapeBoolean(true) . '
				WHERE sender_id = ' . $db->escapeNumber($senderID) . '
				AND game_id = ' . $db->escapeNumber($player->getGameID()) . '
				AND send_time >= ' . $db->escapeNumber($minTime) . '
				AND send_time <= ' . $db->escapeNumber($maxTime) . '
				AND account_id = ' . $db->escapeNumber($player->getAccountID()) . '
				AND message_type_id = ' . $db->escapeNumber(MSG_SCOUT) . '
				AND receiver_delete = ' . $db->escapeBoolean(false));
}

$container = Page::create('skeleton.php', 'message_view.php');
$container->addVar('folder_id');
$container->go();
