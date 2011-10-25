<?php
if (isset($var['alliance_id'])) $alliance_id = $var['alliance_id'];
else $alliance_id = $player->getAllianceID();
$thread_index = $var['thread_index'];
$thread_id = $var['thread_ids'][$thread_index];

if(empty($thread_id))
	create_error('Unable to find thread id.');

$db->query('SELECT leader_id FROM alliance WHERE game_id=' . $player->getAllianceID() . ' AND alliance_id=' . $alliance_id . ' LIMIT 1');
$db->nextRecord();
$template->assign('PageTopic',$var['thread_topics'][$thread_index]);
require_once(get_file_loc('menue.inc'));
create_alliance_menue($alliance_id,$db->getField('leader_id'));

$db->query('REPLACE INTO player_read_thread ' .
		   '(account_id, game_id, alliance_id, thread_id, time)' .
		   'VALUES('.$player->getAccountID().', '.$player->getGameID().', '.$alliance_id.', '.$thread_id.', '.(TIME+2).')');

$mbWrite = true;
if ($alliance_id != $player->getAllianceID())
{
	$db->query('SELECT mb_read FROM alliance_treaties
					WHERE (alliance_id_1 = '.$alliance_id.' OR alliance_id_1 = '.$player->getAllianceID().')'.
					' AND (alliance_id_2 = '.$alliance_id.' OR alliance_id_2 = '.$player->getAllianceID().')'.
					' AND game_id = '.$player->getGameID().
					' AND mb_write = 1 AND official = \'TRUE\'');
	$mbWrite = $db->nextRecord();
}

$container = create_container('skeleton.php','alliance_message_view.php',$var);

if (isset($var['thread_ids'][$thread_index - 1]))
{
	$container['thread_index'] = $thread_index - 1;
	$template->assign('PrevThread',array('Topic' => $var['thread_topics'][$thread_index - 1], 'Href' => SmrSession::get_new_href($container)));
}
if (isset($var['thread_ids'][$thread_index + 1]))
{
	$container['thread_index'] = $thread_index + 1;
	$template->assign('NextThread',array('Topic' => $var['thread_topics'][$thread_index + 1], 'Href' => SmrSession::get_new_href($container)));
}

$thread = array();
$thread['AllianceEyesOnly'] = is_array($var['alliance_eyes']) && $var['alliance_eyes'][$thread_index];
//for report type (system sent) messages
$players[0] = 'Planet Reporter';
$players[-1] = 'Bank Reporter';
$players[-2] = 'Forces Reporter';
$players[-3] = 'Game Admins';
$db->query('SELECT account_id FROM player, alliance_thread ' .
			'WHERE alliance_thread.game_id = '.$player->getGameID().' AND player.game_id = '.$player->getGameID().
			' AND alliance_thread.alliance_id = '.$alliance_id.' AND alliance_thread.thread_id = ' . $thread_id);
while ($db->nextRecord())
{
	$players[$db->getInt('account_id')] = SmrPlayer::getPlayer($db->getInt('account_id'), $player->getGameID())->getLinkedDisplayName(false);
}

$db->query('SELECT mb_messages FROM player_has_alliance_role JOIN alliance_has_roles USING(game_id,alliance_id,role_id) WHERE account_id = '.$player->getAccountID().' AND game_id = '.$player->getGameID().' AND alliance_id='.$alliance_id.' LIMIT 1');
$db->nextRecord();
$thread['CanDelete'] = $db->getBoolean('mb_messages');

$db->query('SELECT text, sender_id, time, reply_id
FROM alliance_thread
WHERE game_id=' .  $player->getGameID() . '
AND alliance_id=' .  $alliance_id . '
AND thread_id=' .  $thread_id . '
ORDER BY reply_id LIMIT ' . $var['thread_replies'][$thread_index]);

$thread['CanDelete'] = $db->getNumRows() > 1 && $thread['CanDelete'];
$thread['Replies'] = array();
$container = create_container('alliance_message_delete_processing.php','',$var);
$container['thread_id'] = $thread_id;
while ($db->nextRecord())
{
	$thread['Replies'][$db->getInt('reply_id')] = array('Sender' => $players[$db->getInt('sender_id')], 'Message' => $db->getField('text'), 'SendTime' => $db->getInt('time'));
	if($thread['CanDelete'])
	{
		$container['reply_id'] = $db->getInt('reply_id');
		$thread['Replies'][$db->getInt('reply_id')]['DeleteHref'] = SmrSession::get_new_href($container);
	}
}

if ($mbWrite || in_array($player->getAccountID(), Globals::getHiddenPlayers()))
{
	$container = create_container('alliance_message_add_processing.php','',$var);
	$container['thread_index'] = $thread_index;
	$thread['CreateThreadReplyFormHref'] = SmrSession::get_new_href($container);
}
$template->assignByRef('Thread',$thread);
if(isset($var['preview']))
	$template->assign('Preview', $var['preview']);
?>