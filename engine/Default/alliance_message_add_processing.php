<?php
$body = htmlentities(trim($_POST['body']),ENT_COMPAT,'utf-8');
$topic = isset($_REQUEST['topic'])?$_REQUEST['topic']:'';
if (isset($_REQUEST['allEyesOnly'])) $allEyesOnly = TRUE;
else $allEyesOnly = FALSE;

if($_REQUEST['action'] == 'Preview Thread' || $_REQUEST['action'] == 'Preview Reply')
{
	$container = create_container('skeleton.php', '', $var);
	if(!isset($var['thread_index']))
		$container['body'] = 'alliance_message.php';
	else
		$container['body'] = 'alliance_message_view.php';
	$container['preview'] = $body;
	$container['topic'] = $topic;
	$container['AllianceEyesOnly'] = $allEyesOnly;
	forward($container);
}

if (isset($var['alliance_id'])) $alliance_id = $var['alliance_id'];
else $alliance_id = $player->getAllianceID();

// it could be we got kicked during writing the msg
if (!$player->hasAlliance())
	create_error('You are not in an alliance anymore');

if (empty($body))
	create_error('You must enter text!');

// if we don't have a thread id
if (!isset($var['thread_index']))
{
	// get one
	$db->query('SELECT max(thread_id) FROM alliance_thread ' .
			   'WHERE game_id = '.$player->getGameID().' AND ' .
			   		 'alliance_id = '.$alliance_id);
	if ($db->nextRecord())
		$thread_id = intval($db->getField('max(thread_id)')) + 1;

}
else
{
	$thread_index = $var['thread_index'];
	$thread_id = $var['thread_ids'][$thread_index];
}

// now get the next reply id
$db->query('SELECT max(reply_id) FROM alliance_thread ' .
		   'WHERE game_id = '.$player->getGameID().' AND ' .
		   		 'alliance_id = '.$alliance_id.' AND ' .
		   		 'thread_id = '.$thread_id);
if ($db->nextRecord())
	$reply_id = intval($db->getField('max(reply_id)')) + 1;

// only add the topic if it's the first reply
if ($reply_id == 1) {

	if (empty($topic))
		create_error('You must enter a topic!');

	if (strlen($topic) > 255)
		create_error('Topic can\'t be longer than 255 chars!');

	// test if this topic already exists
	$db->query('SELECT 1 FROM alliance_thread_topic ' .
			   'WHERE game_id = '.$player->getGameID().' AND ' .
			   		 'alliance_id = '.$alliance_id.' AND ' .
			   		 'topic = ' . $db->escape_string($topic, true));
	if ($db->getNumRows() > 0)
		create_error('This topic exist already!');

	$db->query('INSERT INTO alliance_thread_topic (game_id, alliance_id, thread_id, topic, alliance_only) ' .
										   'VALUES('.$player->getGameID().', '.$alliance_id.', '.$thread_id.', ' . $db->escape_string($topic, true) . ', '.$db->escapeString($allEyesOnly).')');

}

// and the body
$db->query('INSERT INTO alliance_thread (game_id, alliance_id, thread_id, reply_id, text, sender_id, time) ' .
								 'VALUES('.$player->getGameID().', '.$alliance_id.', '.$thread_id.', '.$reply_id.', '.$db->escapeString($body).', '.$player->getAccountID().', ' . TIME . ')');
$db->query('REPLACE INTO player_read_thread ' .
		   '(account_id, game_id, alliance_id, thread_id, time)' .
		   'VALUES('.$player->getAccountID().', '.$player->getGameID().', '.$alliance_id.', '.$thread_id.', '.(TIME+2).')');

$container = create_container('skeleton.php');
$container['alliance_id'] = $alliance_id;
if (isset($var['alliance_eyes'])) $container['alliance_eyes'] = $var['alliance_eyes'];
if(isset($var['thread_index']))
{
	$container['body'] = 'alliance_message_view.php';
	$container['thread_index'] = $thread_index;
	$container['thread_ids'] = $var['thread_ids'];
	$container['thread_topics'] = $var['thread_topics'];
	++$var['thread_replies'][$thread_index];
	$container['thread_replies'] = $var['thread_replies'];
}
else
{
	$container['body'] = 'alliance_message.php';
}

forward($container);

?>