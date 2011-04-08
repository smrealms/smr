<?php
if (isset($var['alliance_id'])) $alliance_id = $var['alliance_id'];
else $alliance_id = $player->getAllianceID();

if(isset($var['reply_id']))
{
	$db->query('DELETE FROM alliance_thread ' .
				'WHERE game_id = '.$player->getGameID() .
				' AND alliance_id = '.$alliance_id .
				' AND thread_id = '.$var['thread_id'] .
				' AND reply_id = '.$var['reply_id'] . ' LIMIT 1');
	forward(create_container('skeleton.php', 'alliance_message_view.php', $var));
}
else
{
	$db->query('DELETE FROM alliance_thread ' .
			   'WHERE game_id = '.$player->getGameID().' AND ' .
			   		 'alliance_id = '.$alliance_id.' AND ' .
			   		 'thread_id = '.$var['thread_id']);
	$db->query('DELETE FROM alliance_thread_topic ' .
			   'WHERE game_id = '.$player->getGameID().' AND ' .
			   		 'alliance_id = '.$alliance_id.' AND ' .
			   		 'thread_id = '.$var['thread_id']);
	forward(create_container('skeleton.php', 'alliance_message.php'));
}
?>