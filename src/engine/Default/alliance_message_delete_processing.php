<?php declare(strict_types=1);
if (!isset($var['alliance_id'])) {
	SmrSession::updateVar('alliance_id', $player->getAllianceID());
}
$alliance_id = $var['alliance_id'];

if (isset($var['reply_id'])) {
	$db->query('DELETE FROM alliance_thread
				WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
				AND alliance_id = ' . $db->escapeNumber($alliance_id) . '
				AND thread_id = ' . $db->escapeNumber($var['thread_id']) . '
				AND reply_id = ' . $db->escapeNumber($var['reply_id']) . ' LIMIT 1');
	Page::create('skeleton.php', 'alliance_message_view.php', $var)->go();
} else {
	$db->query('DELETE FROM alliance_thread
				WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
				AND alliance_id = ' . $db->escapeNumber($alliance_id) . '
				AND thread_id = ' . $db->escapeNumber($var['thread_id']));
	$db->query('DELETE FROM alliance_thread_topic
				WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
				AND alliance_id = ' . $db->escapeNumber($alliance_id) . '
				AND thread_id = ' . $db->escapeNumber($var['thread_id']));
	Page::create('skeleton.php', 'alliance_message.php')->go();
}
