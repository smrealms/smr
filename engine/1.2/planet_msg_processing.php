<?php

//if we dont have an alliance we forward to message box
if ($player->alliance_id == 0) {
	$container = array();
	$container["url"] = "skeleton.php";
	$container["body"] = "message_view.php";
	$container["folder_id"] = MSG_PLANET;
	forward($container);
}
$db2 = new SmrMySqlDatabase();
//check for planet messages
$db->query("SELECT * FROM alliance_thread_topic WHERE alliance_id = $player->alliance_id AND game_id = $player->game_id AND " .
					"topic LIKE 'Planet Attack Report Sector %'");
$container = array();
$msg = array();
while ($db->next_record()) {

	//get the newest post time and such
	$thread_id = $db->f("thread_id");
	$db2->query("SELECT * FROM alliance_thread WHERE thread_id = $thread_id AND alliance_id = $player->alliance_id AND game_id = $player->game_id ORDER BY reply_id DESC");
	$db2->next_record();
	$post_time = $db2->f("time");
	$db2->query("SELECT * FROM player_read_thread WHERE thread_id = $thread_id AND alliance_id = $player->alliance_id AND game_id = $player->game_id AND account_id = $player->account_id");
	if($db2->next_record())
		$time_read = $db2->f("time");
	else
		$time_read = 0;
	if ($time_read < $post_time) {
		$actual_id = $thread_id;
		$msg[] = $thread_id;
	}

}
if (!isset($actual_id)) {
	$player->remove_message("3");
	create_error("An error occured while processing your request.<br><small>This error most likely occured due to your leader deleting the message</small>");
}

if (sizeof($msg) == 1)
	$player->remove_message("3");

$db->query('SELECT 
alliance_thread_topic.alliance_only as alliance_only,
alliance_thread_topic.topic as topic,
alliance_thread.thread_id as thread,
max(alliance_thread.time) as sendtime,
count(alliance_thread.reply_id) as num_replies
FROM alliance_thread_topic,alliance_thread
WHERE alliance_thread.game_id=' . $player->game_id . '
AND alliance_thread_topic.game_id=' . $player->game_id . '
AND alliance_thread_topic.alliance_id=' . $player->alliance_id . '
AND alliance_thread.alliance_id=' . $player->alliance_id . '
AND alliance_thread.thread_id=alliance_thread_topic.thread_id
GROUP BY alliance_thread.thread_id ORDER BY sendtime DESC
');
$alliance_eyes = array();
if ($db->nf() > 0) {
	$i=0;
	while ($db->next_record()) {
		if ($db->f("thread") == $actual_id) $j = $i;
		if ($db->f("alliance_only")) $alliance_eyes[$i] = TRUE;
		else $alliance_eyes[$i] = FALSE;
		$thread_ids[$i] = $db->f('thread');
		$thread_topics[$i] = $db->f('topic');
		$thread_replies[$i] = $db->f('num_replies');
		++$i;
	}
	$db->free();
	$container = array();
	$container['url'] = 'skeleton.php';
	$container['body'] = 'alliance_message_view.php';
	$container['thread_index'] = $j;
	$container['thread_ids'] = $thread_ids;
	$container['thread_topics'] = $thread_topics;
	$container['thread_replies'] = $thread_replies;
	forward($container);
}
?>