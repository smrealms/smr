<?php declare(strict_types=1);

$db = Smr\Database::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();

$body = htmlentities(trim(Smr\Request::get('body')), ENT_COMPAT, 'utf-8');
$topic = Smr\Request::get('topic', ''); // only present for Create Thread
$allEyesOnly = Smr\Request::has('allEyesOnly'); // only present for Create Thread

$action = Smr\Request::get('action');
if ($action == 'Preview Thread' || $action == 'Preview Reply') {
	$container = Page::create('skeleton.php', '', $var);
	if (!isset($var['thread_index'])) {
		$container['body'] = 'alliance_message.php';
	} else {
		$container['body'] = 'alliance_message_view.php';
	}
	$container['preview'] = $body;
	$container['topic'] = $topic;
	$container['AllianceEyesOnly'] = $allEyesOnly;
	$container->go();
}

$alliance_id = $var['alliance_id'] ?? $player->getAllianceID();

// it could be we got kicked during writing the msg
if (!$player->hasAlliance()) {
	create_error('You are not in an alliance!');
}

if (empty($body)) {
	create_error('You must enter text!');
}

// if we don't have a thread id
if (!isset($var['thread_index'])) {
	// get one
	$dbResult = $db->read('SELECT max(thread_id) FROM alliance_thread
				WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
				AND alliance_id = ' . $db->escapeNumber($alliance_id));
	if ($dbResult->hasRecord()) {
		$thread_id = $dbResult->record()->getInt('max(thread_id)') + 1;
	}
} else {
	$thread_index = $var['thread_index'];
	$thread_id = $var['thread_ids'][$thread_index];
}

// now get the next reply id
$dbResult = $db->read('SELECT max(reply_id) FROM alliance_thread
			WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
			AND alliance_id = ' . $db->escapeNumber($alliance_id) . '
			AND thread_id = ' . $db->escapeNumber($thread_id));
if ($dbResult->hasRecord()) {
	$reply_id = $dbResult->record()->getInt('max(reply_id)') + 1;
}

// only add the topic if it's the first reply
if ($reply_id == 1) {
	if (empty($topic)) {
		create_error('You must enter a topic!');
	}

	if (strlen($topic) > 255) {
		create_error('Topic can\'t be longer than 255 chars!');
	}

	// test if this topic already exists
	$dbResult = $db->read('SELECT 1 FROM alliance_thread_topic
				WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
				AND alliance_id = ' . $db->escapeNumber($alliance_id) . '
				AND topic = ' . $db->escapeString($topic));
	if ($dbResult->hasRecord()) {
		create_error('This topic exist already!');
	}

	$db->insert('alliance_thread_topic', [
		'game_id' => $db->escapeNumber($player->getGameID()),
		'alliance_id' => $db->escapeNumber($alliance_id),
		'thread_id' => $db->escapeNumber($thread_id),
		'topic' => $db->escapeString($topic),
		'alliance_only' => $db->escapeBoolean($allEyesOnly),
	]);
}

// and the body
$db->insert('alliance_thread', [
	'game_id' => $db->escapeNumber($player->getGameID()),
	'alliance_id' => $db->escapeNumber($alliance_id),
	'thread_id' => $db->escapeNumber($thread_id),
	'reply_id' => $db->escapeNumber($reply_id),
	'text' => $db->escapeString($body),
	'sender_id' => $db->escapeNumber($player->getAccountID()),
	'time' => $db->escapeNumber(Smr\Epoch::time()),
]);
$db->write('REPLACE INTO player_read_thread
			(account_id, game_id, alliance_id, thread_id, time)
			VALUES(' . $db->escapeNumber($player->getAccountID()) . ', ' . $db->escapeNumber($player->getGameID()) . ', ' . $db->escapeNumber($alliance_id) . ', ' . $db->escapeNumber($thread_id) . ', ' . $db->escapeNumber(Smr\Epoch::time() + 2) . ')');

$container = Page::create('skeleton.php');
$container['alliance_id'] = $alliance_id;
if (isset($var['alliance_eyes'])) {
	$container->addVar('alliance_eyes');
}
if (isset($var['thread_index'])) {
	$container['body'] = 'alliance_message_view.php';
	$container['thread_index'] = $thread_index;
	$container->addVar('thread_ids');
	$container->addVar('thread_topics');
	++$var['thread_replies'][$thread_index];
	$container->addVar('thread_replies');
} else {
	$container['body'] = 'alliance_message.php';
}

$container->go();
