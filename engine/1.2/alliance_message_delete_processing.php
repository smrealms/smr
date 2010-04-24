<?php
if (isset($var['alliance_id'])) $alliance_id = $var['alliance_id'];
else $alliance_id = $player->alliance_id;
$thread_id = $var["thread_id"];

$db->query("DELETE FROM alliance_thread " .
		   "WHERE game_id = $player->game_id AND " .
		   		 "alliance_id = $alliance_id AND " .
		   		 "thread_id = $thread_id");
$db->query("DELETE FROM alliance_thread_topic " .
		   "WHERE game_id = $player->game_id AND " .
		   		 "alliance_id = $alliance_id AND " .
		   		 "thread_id = $thread_id");

forward(create_container("skeleton.php", "alliance_message.php"));

?>