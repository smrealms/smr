<?php declare(strict_types=1);

// Send the bank report to the alliance message board
$alliance_id = $var['alliance_id'];
$text = $var['text'];

// Check if the "Bank Statement" thread exists yet
$db->query('SELECT thread_id FROM alliance_thread_topic WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND alliance_id = ' . $db->escapeNumber($alliance_id) . ' AND topic = \'Bank Statement\' LIMIT 1');

if ($db->nextRecord()) {
	// Update the existing "Bank Statement" thread
	$thread_id = $db->getInt('thread_id');
	$db->query('UPDATE alliance_thread SET time = ' . $db->escapeNumber(TIME) . ', text = ' . $db->escapeString($text) . ' WHERE thread_id = ' . $db->escapeNumber($thread_id) . ' AND alliance_id = ' . $db->escapeNumber($alliance_id) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND reply_id = 1');
	$db->query('DELETE FROM player_read_thread WHERE thread_id = ' . $db->escapeNumber($thread_id) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND alliance_id = ' . $db->escapeNumber($alliance_id));
} else {
	// There is no "Bank Statement" thread yet
	$db->query('SELECT thread_id FROM alliance_thread_topic WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND alliance_id = ' . $db->escapeNumber($alliance_id) . ' ORDER BY thread_id DESC LIMIT 1');
	if ($db->nextRecord()) {
		$thread_id = $db->getField('thread_id') + 1;
	} else {
		$thread_id = 1;
	}
	$db->query('INSERT INTO alliance_thread_topic (game_id, alliance_id, thread_id, topic) VALUES (' . $db->escapeNumber($player->getGameID()) . ', ' . $db->escapeNumber($alliance_id) . ', ' . $db->escapeNumber($thread_id) . ', \'Bank Statement\')');
	$db->query('INSERT INTO alliance_thread (game_id, alliance_id, thread_id, reply_id, text, sender_id, time) VALUES (' . $db->escapeNumber($player->getGameID()) . ', ' . $db->escapeNumber($alliance_id) . ', ' . $db->escapeNumber($thread_id) . ', 1, ' . $db->escapeString($text) . ', ' . $db->escapeNumber(ACCOUNT_ID_BANK_REPORTER) . ', ' . $db->escapeNumber(TIME) . ')');
}

$container = create_container('skeleton.php', 'bank_report.php');
transfer('alliance_id');
$container['sent_report'] = True;
forward($container);
