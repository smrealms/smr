<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$db = Smr\Database::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();

$allianceID = $var['alliance_id'] ?? $player->getAllianceID();

$alliance = SmrAlliance::getAlliance($allianceID, $player->getGameID());
$template->assign('PageTopic', $alliance->getAllianceDisplayName(false, true));
Menu::alliance($alliance->getAllianceID());

$mbWrite = true;
$in_alliance = true;
if ($alliance->getAllianceID() != $player->getAllianceID()) {
	if (!in_array($player->getAccountID(), Globals::getHiddenPlayers())) {
		$in_alliance = false;
	}
	$dbResult = $db->read('SELECT 1 FROM alliance_treaties
				WHERE (alliance_id_1 = ' . $db->escapeNumber($alliance->getAllianceID()) . ' OR alliance_id_1 = ' . $db->escapeNumber($player->getAllianceID()) . ')
				AND (alliance_id_2 = ' . $db->escapeNumber($alliance->getAllianceID()) . ' OR alliance_id_2 = ' . $db->escapeNumber($player->getAllianceID()) . ')
				AND game_id = ' . $db->escapeNumber($player->getGameID()) . '
				AND mb_write = 1 AND official = \'TRUE\' LIMIT 1');
	$mbWrite = $dbResult->hasRecord();
}
$query = 'SELECT
	alliance_only, topic, thread_id,
	max(time) as sendtime,
	min(sender_id) as sender_id,
	count(reply_id) as num_replies
FROM alliance_thread_topic
	JOIN alliance_thread USING(game_id,alliance_id,thread_id)
WHERE game_id=' . $db->escapeNumber($alliance->getGameID()) . '
	AND alliance_id=' . $db->escapeNumber($alliance->getAllianceID());
if (!$in_alliance) {
	$query .= ' AND alliance_only = ' . $db->escapeBoolean(false);
}
$query .= ' GROUP BY thread_id ORDER BY sendtime DESC';
$dbResult = $db->read($query);
$threads = [];
if ($dbResult->hasRecord()) {

	$container = Page::create('alliance_message_delete_processing.php');
	$container['alliance_id'] = $alliance->getAllianceID();

	$i = 0;
	$alliance_eyes = [];
	$thread_ids = [];
	$thread_topics = [];

	foreach ($dbResult->records() as $dbRecord) {
		$threadID = $dbRecord->getInt('thread_id');
		$alliance_eyes[$i] = $dbRecord->getBoolean('alliance_only');
		$threads[$i]['ThreadID'] = $threadID;

		$thread_ids[$i] = $threadID;
		$thread_topics[$i] = $dbRecord->getField('topic');

		$threads[$i]['Topic'] = $dbRecord->getField('topic');

		$dbResult2 = $db->read('SELECT time
					FROM player_read_thread
					WHERE ' . $player->getSQL() . '
					AND alliance_id =' . $db->escapeNumber($alliance->getAllianceID()) . '
					AND thread_id=' . $db->escapeNumber($threadID) . '
					AND time>' . $db->escapeNumber($dbRecord->getInt('sendtime')) . ' LIMIT 1');
		$threads[$i]['Unread'] = !$dbResult2->hasRecord();

		// Determine the thread author display name
		$sender_id = $dbRecord->getInt('sender_id');
		if ($sender_id == ACCOUNT_ID_PLANET) {
			$playerName = 'Planet Reporter';
		} elseif ($sender_id == ACCOUNT_ID_BANK_REPORTER) {
			$playerName = 'Bank Reporter';
		} elseif ($sender_id == ACCOUNT_ID_ADMIN) {
			$playerName = 'Game Admins';
		} else {
			try {
				$author = SmrPlayer::getPlayer($sender_id, $player->getGameID());
				$playerName = $author->getLinkedDisplayName(false);
			} catch (Smr\Exceptions\PlayerNotFound) {
				$playerName = 'Unknown'; // default
			}
		}
		$threads[$i]['Sender'] = $playerName;

		$dbResult2 = $db->read('SELECT * FROM player_has_alliance_role JOIN alliance_has_roles USING(game_id,alliance_id,role_id) WHERE ' . $player->getSQL() . ' AND alliance_id=' . $db->escapeNumber($alliance->getAllianceID()) . ' LIMIT 1');
		$threads[$i]['CanDelete'] = $player->getAccountID() == $sender_id || $dbResult2->record()->getBoolean('mb_messages');
		if ($threads[$i]['CanDelete']) {
			$container['thread_id'] = $threadID;
			$threads[$i]['DeleteHref'] = $container->href();
		}
		$threads[$i]['Replies'] = $dbRecord->getInt('num_replies');
		$threads[$i]['SendTime'] = $dbRecord->getInt('sendtime');
		++$i;
	}

	$container = Page::create('alliance_message_view.php');
	$container['alliance_id'] = $alliance->getAllianceID();
	$container['thread_ids'] = $thread_ids;
	$container['thread_topics'] = $thread_topics;
	$container['alliance_eyes'] = $alliance_eyes;
	for ($j = 0; $j < $i; $j++) {
		$container['thread_index'] = $j;
		$threads[$j]['ViewHref'] = $container->href();
	}
}
$template->assign('Threads', $threads);

if ($mbWrite || in_array($player->getAccountID(), Globals::getHiddenPlayers())) {
	$container = Page::create('alliance_message_add_processing.php');
	$container['alliance_id'] = $alliance->getAllianceID();
	$template->assign('CreateNewThreadFormHref', $container->href());
}

if (isset($var['preview'])) {
	$template->assign('Preview', $var['preview']);
}
if (isset($var['topic'])) {
	$template->assign('Topic', $var['topic']);
}
if (isset($var['AllianceEyesOnly'])) {
	$template->assign('AllianceEyesOnly', $var['AllianceEyesOnly']);
}
