<?php
if (!isset($var['alliance_id'])) {
	SmrSession::updateVar('alliance_id',$player->getAllianceID());
}

$alliance =& SmrAlliance::getAlliance($var['alliance_id'], $player->getGameID());
$template->assign('PageTopic',$alliance->getAllianceName() . ' (' . $alliance->getAllianceID() . ')');
require_once(get_file_loc('menu.inc'));
create_alliance_menu($alliance->getAllianceID(),$alliance->getLeaderID());
$mbWrite = TRUE;
$in_alliance = TRUE;
if ($alliance->getAllianceID() != $player->getAllianceID()) {
	if (!in_array($player->getAccountID(), Globals::getHiddenPlayers())) {
		$in_alliance = FALSE;
	}
	$db->query('SELECT mb_read FROM alliance_treaties
				WHERE (alliance_id_1 = ' . $db->escapeNumber($alliance->getAllianceID()) . ' OR alliance_id_1 = ' . $db->escapeNumber($player->getAllianceID()) . ')
				AND (alliance_id_2 = ' . $db->escapeNumber($alliance->getAllianceID()) . ' OR alliance_id_2 = ' . $db->escapeNumber($player->getAllianceID()) . ')
				AND game_id = ' . $db->escapeNumber($player->getGameID()) . '
				AND mb_write = 1 AND official = \'TRUE\' LIMIT 1');
	$mbWrite = $db->nextRecord();
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
	$query .= ' AND alliance_only = 0';
}
$query .= ' GROUP BY thread_id ORDER BY sendtime DESC';
$db->query($query);
$threads = array();
if ($db->getNumRows() > 0) {
	$db2 = new SmrMySqlDatabase();

	$container = create_container('alliance_message_delete_processing.php');
	$container['alliance_id'] = $alliance->getAllianceID();

	$i=0;
	$alliance_eyes = array();
	while ($db->nextRecord()) {
		$alliance_eyes[$i] = $db->getInt('alliance_only') == 1;
		$threads[$i]['thread_id'] = $db->getInt('thread_id');

		$thread_ids[$i] = $db->getInt('thread_id');
		$thread_topics[$i] = $db->getField('topic');

		$threads[$i]['Topic'] = $db->getField('topic');
		
		$db2->query('SELECT time
					FROM player_read_thread 
					WHERE account_id=' . $db2->escapeNumber($player->getAccountID()) . '
					AND game_id=' . $db2->escapeNumber($player->getGameID()) . '
					AND alliance_id =' . $db2->escapeNumber($alliance->getAllianceID()) . '
					AND thread_id=' . $db2->escapeNumber($db->getInt('thread_id')) . ' 
					AND time>' . $db2->escapeNumber($db->getInt('sendtime')) . ' LIMIT 1');
		$threads[$i]['Unread'] = $db2->getNumRows() == 0;
		
		if ($db->getInt('sender_id') > 0) {
			$db2->query('SELECT
						player.player_name as player_name,
						alliance_thread.sender_id as sender_id
						FROM player
						JOIN alliance_thread ON alliance_thread.game_id = player.game_id AND player.account_id=alliance_thread.sender_id
						WHERE player.game_id=' . $db2->escapeNumber($player->getGameID()) . '
						AND alliance_thread.alliance_id=' . $db2->escapeNumber($alliance->getAllianceID()) . '
						AND alliance_thread.thread_id=' . $db2->escapeNumber($db->getInt('thread_id')) . '
						AND alliance_thread.reply_id=1 LIMIT 1
						');
			if($db2->nextRecord()) {
				$sender_id = $db2->getInt('sender_id');
				$author =& SmrPlayer::getPlayer($sender_id, $player->getGameID());
				$playerName = $author->getLinkedDisplayName(false);
			}
			else {
				$playerName = 'Unknown';
			}
		}
		else {
			$sender_id = $db->getInt('sender_id');
			if ($sender_id == 0) $playerName = 'Planet Reporter';
			if ($sender_id == -1) $playerName = 'Bank Reporter';
			if ($sender_id == -2) $playerName = 'Forces Reporter';
			if ($sender_id == -3) $playerName = 'Game Admins';
		}
		$threads[$i]['Sender'] = $playerName;

		$db2->query('SELECT * FROM player_has_alliance_role JOIN alliance_has_roles USING(game_id,alliance_id,role_id) WHERE account_id = ' . $db->escapeNumber($player->getAccountID()) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND alliance_id=' . $db->escapeNumber($alliance->getAllianceID()) . ' LIMIT 1');
		$db2->nextRecord();
		$threads[$i]['CanDelete'] = $player->getAccountID() == $sender_id || $db2->getBoolean('mb_messages');
		if($threads[$i]['CanDelete']) {
			$container['thread_id'] = $db->getInt('thread_id');
			$threads[$i]['DeleteHref'] = SmrSession::get_new_href($container);
		}
		$threads[$i]['Replies'] = $db->getInt('num_replies');
		$thread_replies[$i] = $db->getInt('num_replies');
		$threads[$i]['SendTime'] = $db->getInt('sendtime');
		++$i;
	}

	$container = create_container('skeleton.php','alliance_message_view.php');
	$container['alliance_id'] = $alliance->getAllianceID();
	$container['thread_ids'] = $thread_ids;
	$container['thread_topics'] = $thread_topics;
	$container['thread_replies'] = $thread_replies;
	$container['alliance_eyes'] = $alliance_eyes;
	for($j=0;$j<$i;$j++) {
		$container['thread_index'] = $j;
		$threads[$j]['ViewHref'] = SmrSession::get_new_href($container);
	}
}
$template->assignByRef('Threads',$threads);

if ($mbWrite || in_array($player->getAccountID(), Globals::getHiddenPlayers())) {
	$container = create_container('alliance_message_add_processing.php');
	$container['alliance_id'] = $alliance->getAllianceID();
	$template->assign('CreateNewThreadFormHref',SmrSession::get_new_href($container));
}

if(isset($var['preview'])) {
	$template->assign('Preview', $var['preview']);
}
if(isset($var['topic'])) {
	$template->assign('Topic', $var['topic']);
}
if(isset($var['AllianceEyesOnly'])) {
	$template->assign('AllianceEyesOnly', $var['AllianceEyesOnly']);
}
?>