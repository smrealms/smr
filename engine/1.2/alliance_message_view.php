<?php
if (isset($var['alliance_id'])) $alliance_id = $var['alliance_id'];
else $alliance_id = $player->alliance_id;
$thread_index = $var['thread_index'];
$thread_id = $var['thread_ids'][$thread_index];

$db->query('SELECT leader_id FROM alliance WHERE game_id=' . SmrSession::$game_id . ' AND alliance_id=' . $alliance_id . ' LIMIT 1');
$db->next_record();
print_topic(stripslashes($var['thread_topics'][$thread_index]));
require_once(get_file_loc('menu.inc'));
print_alliance_menue($alliance_id,$db->f('leader_id'));

$curr_time = time() + 2;

$db->query("REPLACE INTO player_read_thread " .
		   "(account_id, game_id, alliance_id, thread_id, time)" .
		   "VALUES($player->account_id, $player->game_id, $alliance_id, $thread_id, $curr_time)");

$mbWrite = TRUE;
if ($alliance_id != $player->alliance_id) {
	$db->query("SELECT mb_read FROM alliance_treaties
					WHERE (alliance_id_1 = $alliance_id OR alliance_id_1 = $player->alliance_id)
					AND (alliance_id_2 = $alliance_id OR alliance_id_2 = $player->alliance_id)
					AND game_id = $player->game_id
					AND mb_write = 1 AND official = 'TRUE'");
	if ($db->next_record()) $mbWrite = TRUE;
	else $mbWrite = FALSE;
}

$container = array();
$container['url'] = 'skeleton.php';
$container['body'] = 'alliance_message_view.php';
$container['alliance_id'] = $alliance_id;
$container['thread_ids'] = $var['thread_ids'];
$container['thread_topics'] = $var['thread_topics'];
$container['thread_replies'] = $var['thread_replies'];
if (isset($var['alliance_eyes']))
	$container['alliance_eyes'] = $var['alliance_eyes'];

if(
	isset($var['thread_ids'][$thread_index - 1]) ||
	isset($var['thread_ids'][$thread_index + 1]) 
) {
	echo '<h2>Switch Topic</h2><br><table cellspacing="0" cellpadding="0" class="nobord fullwidth">';
	if (isset($var['thread_ids'][$thread_index - 1])) {
		echo '<tr><td style="text-align:left">';
		$container['thread_index'] = $thread_index - 1;
		print_link($container, '<img src="images/album/rew.jpg" alt="Previous" title="Previous">');
		//echo '</td><td style="vertical-align:middle">';
		echo'&nbsp;&nbsp;';
		echo stripslashes($var['thread_topics'][$thread_index - 1]);
		echo '</td>';
	} else echo '<tr><td>&nbsp;</td>';
	if (isset($var['thread_ids'][$thread_index + 1])) {
		echo '<td style="text-align:right">';
		$container['thread_index'] = $thread_index + 1;
		echo stripslashes($var['thread_topics'][$thread_index + 1]);
		//echo '</td><td style="vertical-align:middle">';
		echo'&nbsp;&nbsp;';
		print_link($container, '<img src="images/album/fwd.jpg" alt="Next" title="Next">');
		echo '</td></tr>';
	} else echo '<td>&nbsp;</td></tr>';
	echo '</table><br>';
}

echo '<h2>Messages</h2><div align="center">';
if (is_array($var['alliance_eyes']) && $var['alliance_eyes'][$thread_index]) echo '<br>Note: This topic is for alliance eyes only.';
echo '<br><table cellspacing="0" cellpadding="0" class="standard inset"><tr><th>Author</th><th>Message</th><th>Time</th></tr>';
//for report type (system sent) messages
$players[0] = 'Planet Reporter';
$players[-1] = 'Bank Reporter';
$players[-2] = 'Forces Reporter';
$players[-3] = 'Game Admins';
$db->query("SELECT account_id as id, player_name as name FROM player, alliance_thread " . 
			"WHERE alliance_thread.game_id = $player->game_id AND player.game_id = $player->game_id " .
			"AND alliance_thread.alliance_id = $alliance_id AND alliance_thread.thread_id = " .
			$thread_id);
while ($db->next_record()) $players[$db->f("id")] = stripslashes($db->f("name"));

$db->query('SELECT 
alliance_thread.text as text,
alliance_thread.sender_id as sender_id,
alliance_thread.time as sendtime
FROM alliance_thread
WHERE alliance_thread.game_id=' .  $player->game_id . '
AND alliance_thread.alliance_id=' .  $alliance_id . '
AND alliance_thread.thread_id=' .  $thread_id . '
ORDER BY reply_id LIMIT ' . $var['thread_replies'][$thread_index]);

while ($db->next_record()) {
	echo '<tr>';
	echo '<td class="shrink nowrap top">';
	echo $players[$db->f("sender_id")];
	echo '</td>';
	echo '<td>';
	echo stripslashes($db->f("text"));
	echo '</td>';
	echo '<td class="shrink nowrap top">';
	echo date('n/j/Y g:i:s A', $db->f('sendtime'));
	echo '</td></tr>';
}

print("</table></div>");

if ($mbWrite || in_array($player->account_id, $HIDDEN_PLAYERS)) {
	echo '<br><h2>Create Reply</h2><br>';
	$container = array();
	$container['url'] = 'alliance_message_add_processing.php';
	$container['alliance_id'] = $alliance_id;
	if (isset($var['alliance_eyes']))
		$container['alliance_eyes'] = $var['alliance_eyes'];
	$container['thread_index'] = $thread_index;
	$container['thread_ids'] = $var['thread_ids'];
	$container['thread_topics'] = $var['thread_topics'];
	$container['thread_replies'] = $var['thread_replies'];
	$form = create_form($container,'Create Reply');
	echo $form['form'];
	echo '
	<table cellspacing="0" cellpadding="0" class="nobord nohpad">
		<tr>
			<td class="top">Body:&nbsp;</td>
			<td><textarea name="body"></textarea></td>
		</tr>
	</table><br>
	';
	echo $form['submit'];
	echo '</form>';
}
?>