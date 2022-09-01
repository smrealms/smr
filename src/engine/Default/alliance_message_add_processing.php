<?php declare(strict_types=1);

use Smr\Database;
use Smr\Epoch;
use Smr\Request;

$db = Database::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();

$body = htmlentities(Request::get('body'), ENT_COMPAT, 'utf-8');
$topic = Request::get('topic', ''); // only present for Create Thread
$allEyesOnly = Request::has('allEyesOnly'); // only present for Create Thread

$action = Request::get('action');
if ($action == 'Preview Thread' || $action == 'Preview Reply') {
	if (!isset($var['thread_index'])) {
		$container = Page::create('alliance_message.php', $var);
	} else {
		$container = Page::create('alliance_message_view.php', $var);
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
	$dbResult = $db->read('SELECT IFNULL(max(thread_id)+1, 0) AS next_thread_id FROM alliance_thread
				WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
				AND alliance_id = ' . $db->escapeNumber($alliance_id));
	$thread_id = $dbResult->record()->getInt('next_thread_id');
} else {
	$thread_id = $var['thread_ids'][$var['thread_index']];
}

// now get the next reply id
$dbResult = $db->read('SELECT IFNULL(max(reply_id)+1, 0) AS next_reply_id FROM alliance_thread
			WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
			AND alliance_id = ' . $db->escapeNumber($alliance_id) . '
			AND thread_id = ' . $db->escapeNumber($thread_id));
$reply_id = $dbResult->record()->getInt('next_reply_id');

// only add the topic if it's the first reply
if ($reply_id == 0) {
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
		create_error('This topic exists already!');
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
	'time' => $db->escapeNumber(Epoch::time()),
]);
$db->replace('player_read_thread', [
	'account_id' => $db->escapeNumber($player->getAccountID()),
	'game_id' => $db->escapeNumber($player->getGameID()),
	'alliance_id' => $db->escapeNumber($alliance_id),
	'thread_id' => $db->escapeNumber($thread_id),
	'time' => $db->escapeNumber(Epoch::time() + 2),
]);

if (isset($var['thread_index'])) {
	$container = Page::create('alliance_message_view.php');
	$container->addVar('thread_index');
	$container->addVar('thread_ids');
	$container->addVar('thread_topics');
} else {
	$container = Page::create('alliance_message.php');
}
if (isset($var['alliance_eyes'])) {
	$container->addVar('alliance_eyes');
}
$container['alliance_id'] = $alliance_id;

$container->go();
