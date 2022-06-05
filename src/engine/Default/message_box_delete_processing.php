<?php declare(strict_types=1);

$db = Smr\Database::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();

if ($var['folder_id'] == MSG_SENT) {
	$db->write('UPDATE message SET sender_delete = ' . $db->escapeBoolean(true) . '
				WHERE sender_id = ' . $db->escapeNumber($player->getAccountID()) . '
					AND game_id = ' . $db->escapeNumber($player->getGameID()));
} else {
	$db->write('UPDATE message SET receiver_delete = ' . $db->escapeBoolean(true) . '
				WHERE account_id = ' . $db->escapeNumber($player->getAccountID()) . '
					AND game_id = ' . $db->escapeNumber($player->getGameID()) . '
					AND message_type_id = ' . $db->escapeNumber($var['folder_id']) . '
					AND msg_read = ' . $db->escapeBoolean(true));
}

Page::create('message_box.php')->go();
