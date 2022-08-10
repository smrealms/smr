<?php declare(strict_types=1);

use Smr\Database;

		$db = Database::getInstance();
		$session = Smr\Session::getInstance();
		$var = $session->getCurrentVar();
		$player = $session->getPlayer();

		$alliance_id = $var['alliance_id'] ?? $player->getAllianceID();

		if (isset($var['reply_id'])) {
			$db->write('DELETE FROM alliance_thread
						WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
						AND alliance_id = ' . $db->escapeNumber($alliance_id) . '
						AND thread_id = ' . $db->escapeNumber($var['thread_id']) . '
						AND reply_id = ' . $db->escapeNumber($var['reply_id']));
			Page::create('alliance_message_view.php', $var)->go();
		} else {
			$db->write('DELETE FROM alliance_thread
						WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
						AND alliance_id = ' . $db->escapeNumber($alliance_id) . '
						AND thread_id = ' . $db->escapeNumber($var['thread_id']));
			$db->write('DELETE FROM alliance_thread_topic
						WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
						AND alliance_id = ' . $db->escapeNumber($alliance_id) . '
						AND thread_id = ' . $db->escapeNumber($var['thread_id']));
			Page::create('alliance_message.php', ['alliance_id' => $alliance_id])->go();
		}
