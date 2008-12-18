<?

if (isset($var['alliance_id'])) $alliance_id = $var['alliance_id'];
else $alliance_id = '.$player->getAllianceID().';
$db->query('SELECT leader_id, alliance_id, alliance_name FROM alliance WHERE game_id=' . SmrSession::$game_id . ' AND alliance_id=' . $alliance_id . ' LIMIT 1');
$db->next_record();
$smarty->assign('PageTopic',stripslashes($db->f('alliance_name')) . ' (' . $db->f('alliance_id') . ')');
include(ENGINE . 'global/menue.inc');
$PHP_OUTPUT.=create_alliance_menue($alliance_id,$db->f('leader_id'));
$mbWrite = TRUE;
$in_alliance = TRUE;
if ($alliance_id != $player->getAllianceID()) {
	if (!in_array($player->getAccountID(), $HIDDEN_PLAYERS)) $in_alliance = FALSE;
	$db->query('SELECT mb_read FROM alliance_treaties
					WHERE (alliance_id_1 = '.$alliance_id.' OR alliance_id_1 = '.$player->getAllianceID().')'.
					' AND (alliance_id_2 = '.$alliance_id.' OR alliance_id_2 = '.$player->getAllianceID().')'.
					' AND game_id = '.$player->getGameID().
					' AND mb_write = 1 AND official = \'TRUE\'');
	if ($db->next_record()) $mbWrite = TRUE;
	else $mbWrite = FALSE;
}
$query = 'SELECT 
alliance_thread_topic.alliance_only as alliance_only,
alliance_thread_topic.topic as topic,
alliance_thread.thread_id as thread,
max(alliance_thread.time) as sendtime,
min(alliance_thread.sender_id) as sender_id,
count(alliance_thread.reply_id) as num_replies
FROM alliance_thread_topic,alliance_thread
WHERE alliance_thread.game_id=' . $player->getGameID() . '
AND alliance_thread_topic.game_id=' . $player->getGameID() . '
AND alliance_thread_topic.alliance_id=' . $alliance_id . '
AND alliance_thread.alliance_id=' . $alliance_id . '
AND alliance_thread.thread_id=alliance_thread_topic.thread_id';
if (!$in_alliance) $query .= ' AND alliance_thread_topic.alliance_only = 0';
$query .= ' GROUP BY alliance_thread.thread_id ORDER BY sendtime DESC';
$db->query($query);
if ($db->nf() > 0) {
	$PHP_OUTPUT.= '<div align="center">';
	$PHP_OUTPUT.= '<table cellspacing="0" cellpadding="0" class="standard inset"><tr><th>Topic</th><th>Author</th><th>Replies</th><th>Last Reply</th></tr>';

	$db2 = new SMR_DB();
	$db3 = new SMR_DB();
	$threads = array();

	$container = array();
	$container['url'] = 'alliance_message_delete_processing.php';
	$container['alliance_id'] = $alliance_id;

	$i=0;
	$alliance_eyes = array();
	while ($db->next_record()) {
		
		$db2->query('SELECT time
					FROM player_read_thread 
					WHERE account_id=' . $player->getAccountID()  . '
					AND game_id=' . $player->getGameID() . '
					AND alliance_id =' . $alliance_id . '
					AND thread_id=' . $db->f('thread') . ' 
					AND time>' . $db->f('sendtime') . ' LIMIT 1
					');
		if ($db->f('alliance_only')) $alliance_eyes[$i] = TRUE;
		else $alliance_eyes[$i] = FALSE;
		$threads[$i]['head'] =  '<tr><td>';
		$threads[$i]['tail'] = '';
		$threads[$i]['thread_id'] = $db->f('thread');

		$thread_ids[$i] = $db->f('thread');
		$thread_topics[$i] = $db->f('topic');

		if ($db2->nf() == 0) {
			$threads[$i]['head'] .= '<b>';
			$threads[$i]['tail'] .= '</b>';
		}
		if ($db->f('sender_id') > 0) {
			$db2->query('SELECT
						player.player_name as player_name,
						alliance_thread.sender_id as sender_id
						FROM alliance_thread,player
						WHERE player.game_id=' . $player->getGameID() . '
						AND alliance_thread.game_id=' . $player->getGameID() . '
						AND alliance_thread.alliance_id=' . $alliance_id . '
						AND alliance_thread.thread_id=' . $db->f('thread') . '
						AND alliance_thread.reply_id=1
						AND player.account_id=alliance_thread.sender_id LIMIT 1
						');
	
			$db2->next_record();
			$playerName = stripslashes($db2->f('player_name'));
			$sender_id = $db2->f('sender_id');
		} else {
			$sender_id = $db->f('sender_id');
			if ($sender_id == 0) $playerName = 'Planet Reporter';
			if ($sender_id == -1) $playerName = 'Bank Reporter';
			if ($sender_id == -2) $playerName = 'Forces Reporter';
			if ($sender_id == -3) $playerName = 'Game Admins';
		}

		$threads[$i]['tail'] .= '</td><td class="shrink nowrap">';
		$threads[$i]['tail'] .= stripslashes($playerName);
		$db3->query('SELECT * FROM player_has_alliance_role WHERE account_id = '.$player->getAccountID().' AND game_id = '.$player->getGameID());
		if ($db3->next_record()) $role_id = $db3->f('role_id');
		else $role_id = 0;
		$db3->query('SELECT * FROM alliance_has_roles WHERE alliance_id = '.$alliance_id.' AND game_id = '.$player->getGameID().' AND role_id = '.$role_id);
		$db3->next_record();
		if ($player->getAccountID() == $sender_id || $db3->f('mb_messages')) {
			$container['thread_id'] = $db->f('thread');
			$threads[$i]['tail'] .= '<br><small>';
			$threads[$i]['tail'] .= create_link($container, 'Delete Thread!');
			$threads[$i]['tail'] .= '</small>';
		}
		$threads[$i]['tail'] .= '</td><td class="shrink center">';
		$threads[$i]['tail'] .= $db->f('num_replies');
		$thread_replies[$i] = $db->f('num_replies');
		$threads[$i]['tail'] .= '</td><td class="shrink nowrap">';
		$threads[$i]['tail'] .= date('n/j/Y g:i:s A', $db->f('sendtime'));
		$threads[$i]['tail'] .= '</td></tr>';
		++$i;
	}

	$container = array();
	$container['url'] = 'skeleton.php';
	$container['body'] = 'alliance_message_view.php';
	$container['alliance_id'] = $alliance_id;

	for($j=0;$j<$i;$j++) {
		$container['thread_index'] = $j;
		$container['thread_ids'] = $thread_ids;
		$container['thread_topics'] = $thread_topics;
		$container['thread_replies'] = $thread_replies;
		$container['alliance_eyes'] = $alliance_eyes;
		$PHP_OUTPUT.= $threads[$j]['head'];
		$PHP_OUTPUT.=create_link($container,stripslashes($thread_topics[$j]));
		$PHP_OUTPUT.= $threads[$j]['tail'];
	}
		

	$PHP_OUTPUT.= '</table></div><br>';
	$db2->free();
	$db->free();
}

if ($mbWrite || in_array($player->getAccountID(), $HIDDEN_PLAYERS)) {
	$PHP_OUTPUT.= '<h2>Create Thread</h2><br>';
	$container = array();
	$container['url'] = 'alliance_message_add_processing.php';
	$container['alliance_id'] = $alliance_id;
	$form = create_form($container,'New Thread');
	$PHP_OUTPUT.= $form['form'];
	$PHP_OUTPUT.= '
	<table cellspacing="0" cellpadding="0" class="nobord nohpad">
		<tr>
			<td class="top">Topic:&nbsp;</td>
			<td class="mb"><input type="text" name="topic" size="30"></td>
			<td style="text-align:left;">For Alliance Eyes Only:<input id="InputFields" name="allEyesOnly" type="checkbox"></td>
		</tr>
		<tr>
			<td class="top">Body:&nbsp;</td>
			<td colspan="2"><textarea name="body"></textarea></td>
		</tr>
	</table><br>
	';
	$PHP_OUTPUT.= $form['submit'];
	$PHP_OUTPUT.= '</form>';
}

?>