<?
if (isset($var['alliance_id'])) $alliance_id = $var['alliance_id'];
else $alliance_id = $player->getAllianceID();
$thread_index = $var['thread_index'];
$thread_id = $var['thread_ids'][$thread_index];

if(empty($thread_id))
	create_error('Unable to find thread id.');

$db->query('SELECT leader_id FROM alliance WHERE game_id=' . SmrSession::$game_id . ' AND alliance_id=' . $alliance_id . ' LIMIT 1');
$db->nextRecord();
$template->assign('PageTopic',stripslashes($var['thread_topics'][$thread_index]));
include(get_file_loc('menue.inc'));
$PHP_OUTPUT.=create_alliance_menue($alliance_id,$db->getField('leader_id'));

$db->query('REPLACE INTO player_read_thread ' .
		   '(account_id, game_id, alliance_id, thread_id, time)' .
		   'VALUES('.$player->getAccountID().', '.$player->getGameID().', '.$alliance_id.', '.$thread_id.', '.(TIME+2).')');

$mbWrite = TRUE;
if ($alliance_id != $player->getAllianceID()) {
	$db->query('SELECT mb_read FROM alliance_treaties
					WHERE (alliance_id_1 = '.$alliance_id.' OR alliance_id_1 = '.$player->getAllianceID().')'.
					' AND (alliance_id_2 = '.$alliance_id.' OR alliance_id_2 = '.$player->getAllianceID().')'.
					' AND game_id = '.$player->getGameID().
					' AND mb_write = 1 AND official = \'TRUE\'');
	if ($db->nextRecord()) $mbWrite = TRUE;
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
	$PHP_OUTPUT.= '<h2>Switch Topic</h2><br /><table cellspacing="0" cellpadding="0" class="nobord fullwidth">';
	if (isset($var['thread_ids'][$thread_index - 1])) {
		$PHP_OUTPUT.= '<tr><td style="text-align:left">';
		$container['thread_index'] = $thread_index - 1;
		$PHP_OUTPUT.=create_link($container, '<img src="images/album/rew.jpg" alt="Previous" title="Previous">');
		//$PHP_OUTPUT.= '</td><td style='vertical-align:middle'>';
		$PHP_OUTPUT.='&nbsp;&nbsp;';
		$PHP_OUTPUT.= stripslashes($var['thread_topics'][$thread_index - 1]);
		$PHP_OUTPUT.= '</td>';
	} else $PHP_OUTPUT.= '<tr><td>&nbsp;</td>';
	if (isset($var['thread_ids'][$thread_index + 1])) {
		$PHP_OUTPUT.= '<td style="text-align:right">';
		$container['thread_index'] = $thread_index + 1;
		$PHP_OUTPUT.= stripslashes($var['thread_topics'][$thread_index + 1]);
		//$PHP_OUTPUT.= '</td><td style='vertical-align:middle'>';
		$PHP_OUTPUT.='&nbsp;&nbsp;';
		$PHP_OUTPUT.=create_link($container, '<img src="images/album/fwd.jpg" alt="Next" title="Next">');
		$PHP_OUTPUT.= '</td></tr>';
	} else $PHP_OUTPUT.= '<td>&nbsp;</td></tr>';
	$PHP_OUTPUT.= '</table><br />';
}

$PHP_OUTPUT.= '<h2>Messages</h2><div align="center">';
if (is_array($var['alliance_eyes']) && $var['alliance_eyes'][$thread_index]) $PHP_OUTPUT.= '<br />Note: This topic is for alliance eyes only.';
$PHP_OUTPUT.= '<br /><table class="standard inset"><tr><th>Author</th><th>Message</th><th>Time</th></tr>';
//for report type (system sent) messages
$players[0] = 'Planet Reporter';
$players[-1] = 'Bank Reporter';
$players[-2] = 'Forces Reporter';
$players[-3] = 'Game Admins';
$db->query('SELECT account_id as id, player_name as name FROM player, alliance_thread ' . 
			'WHERE alliance_thread.game_id = '.$player->getGameID().' AND player.game_id = '.$player->getGameID().' ' .
			'AND alliance_thread.alliance_id = '.$alliance_id.' AND alliance_thread.thread_id = ' .
			$thread_id);
while ($db->nextRecord()) $players[$db->getField('id')] = stripslashes($db->getField('name'));

$db->query('SELECT 
alliance_thread.text as text,
alliance_thread.sender_id as sender_id,
alliance_thread.time as sendtime
FROM alliance_thread
WHERE alliance_thread.game_id=' .  $player->getGameID() . '
AND alliance_thread.alliance_id=' .  $alliance_id . '
AND alliance_thread.thread_id=' .  $thread_id . '
ORDER BY reply_id LIMIT ' . $var['thread_replies'][$thread_index]);

while ($db->nextRecord()) {
	$PHP_OUTPUT.= '<tr>';
	$PHP_OUTPUT.= '<td class="shrink nowrap top">';
	$PHP_OUTPUT.= $players[$db->getField('sender_id')];
	$PHP_OUTPUT.= '</td>';
	$PHP_OUTPUT.= '<td>';
	$PHP_OUTPUT.= bbifyMessage($db->getField('text'));
	$PHP_OUTPUT.= '</td>';
	$PHP_OUTPUT.= '<td class="shrink nowrap top">';
	$PHP_OUTPUT.= date(DATE_FULL_SHORT, $db->getField('sendtime'));
	$PHP_OUTPUT.= '</td></tr>';
}

$PHP_OUTPUT.=('</table></div>');

if ($mbWrite || in_array($player->getAccountID(), $HIDDEN_PLAYERS)) {
	$PHP_OUTPUT.= '<br /><h2>Create Reply</h2><br />';
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
	$PHP_OUTPUT.= $form['form'];
	$PHP_OUTPUT.= '
	<table cellspacing="0" cellpadding="0" class="nobord nohpad">
		<tr>
			<td class="top">Body:&nbsp;</td>
			<td><textarea name="body"></textarea></td>
		</tr>
	</table><br />
	';
	$PHP_OUTPUT.= $form['submit'];
	$PHP_OUTPUT.= '</form>';
}
?>