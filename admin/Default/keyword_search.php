<?php

//script to search alliance message boards and personal messages for cheats/loopholes
//first get our keywords
$db->query('SELECT * FROM mb_keywords WHERE type = \'find\' AND `use` = 1');
$db2 = new SmrMySqlDatabase();
$db3 = new SmrMySqlDatabase();
$db4 = new SmrMySqlDatabase();
if(isset($var['errorMsg'])) $PHP_OUTPUT.=($var['errorMsg'] . '<br /><br />');
if (isset($var['msg'])) $PHP_OUTPUT.=($var['msg'] . '<br /><br />');

$container = create_container('keyword_processing.php');
$container['type'] = 'alliance';
$PHP_OUTPUT.=create_echo_form($container);
//count of messages
$count = 0;
//array for mb so we dont duplicate
$mb_msgs = array();
while ($db->nextRecord()) {

	//search every message on webboards for each word first
	$id = $db->getField('id');
	$word = $db->getField('keyword');

	$db2->query('SELECT * FROM alliance_thread WHERE sender_id != 0 AND text LIKE ' . $db2->escapeString('%'.$word.'%') . ' ORDER BY time DESC');
	while ($db2->nextRecord()) {
		//assume we arent skipping
		$skip = 'no';
		$bad = $db2->getField('text');
		$db3->query('SELECT * FROM mb_keywords WHERE assoc = '.$db3->escapeNumber($id).' AND type = \'ignore\' AND `use` = 1');
		while ($db3->nextRecord()) {
			$word2 = $db3->getField('keyword');
			$db4->query('SELECT '.$db->escapeString($bad).' LIKE ' . $db4->escapeString('%'.$word2.'%'));
			$db4->nextRecord();
			if ($db4->getField(0)) $skip = 'yes';
		}
		if ($skip == 'yes') continue;
		//get info
		$game_id = $db2->getField('game_id');
		$alliance_id = $db2->getField('alliance_id');
		$thread_id = $db2->getField('thread_id');
		$reply_id = $db2->getField('reply_id');
		//put in an array
		$array_filler = $game_id.','.$alliance_id.','.$thread_id.','.$reply_id;
		//check if its already been done
		if (in_array($array_filler,$mb_msgs))
			continue;
		else
			$mb_msgs[] = $array_filler;
		//check if msg is okay or not
		$db3->query('SELECT * FROM mb_exceptions WHERE type = \'alliance\' AND value = '.$db->escapeString($array_filler));
		if ($db3->getNumRows())
			continue;
		//only if this is first message found
		if ($count == 0) {
			//start table
			$PHP_OUTPUT.=('<a href=#button1>Goto Exception Button/Personal Messages Start</a>');
			$PHP_OUTPUT.= create_table();
			$PHP_OUTPUT.=('<tr>');
			$PHP_OUTPUT.=('<th>Game ID</th>');
			$PHP_OUTPUT.=('<th>Alliance ID</th>');
			$PHP_OUTPUT.=('<th>Thread ID</th>');
			$PHP_OUTPUT.=('<th>Reply ID</th>');
			$PHP_OUTPUT.=('<th>Sender ID</th>');
			$PHP_OUTPUT.=('<th>Bad text</th>');
			$PHP_OUTPUT.=('<th>Ignore</th>');
			$PHP_OUTPUT.=('</tr>');
		}

		//lets echo this message
		$PHP_OUTPUT.=('<tr>');
		$PHP_OUTPUT.=('<td class="center">'.$game_id.'</td>');
		$PHP_OUTPUT.=('<td class="center">'.$alliance_id.'</td>');
		$PHP_OUTPUT.=('<td class="center">'.$thread_id.'</td>');
		$PHP_OUTPUT.=('<td class="center">'.$reply_id.'</td>');
		//make sure we check for Word, WORD, and word...after phpv5 use str_ireplace
		$array = array();
		$array[] = ucfirst($word);
		$array[] = strtoupper($word);
		$array[] = strtolower($word);
		$bad = str_replace($array, '<b><span class="red">'.$word.'</span></b>', $db->escapeString($bad));
		$PHP_OUTPUT.=('<td class="center">' . $db2->getField('sender_id') . '</td>');
		$PHP_OUTPUT.=('<td class="center">'.$bad.'</td>');
		$PHP_OUTPUT.=('<td class="center"><input type=checkbox name=alliance[] value='.$array_filler.'></td>');
		$PHP_OUTPUT.=('</tr>');
		//update count
		$count += 1;

	}
}

if ($count == 0)
	$PHP_OUTPUT.=('No harmful messages found on alliance webboards<br /></form>');
else {
	$PHP_OUTPUT.=('</table><br />');
	$PHP_OUTPUT.=('<a name=button1>');
	$PHP_OUTPUT.=create_submit('Add To Exception Table');
	$PHP_OUTPUT.=('</a></form>');
}
$PHP_OUTPUT.=('<br /><br />');

$container = array();
$container['url'] = 'keyword_processing.php';
$container['type'] = 'personal';
$PHP_OUTPUT.=create_echo_form($container);
//start another search for messages
$db->query('SELECT * FROM mb_keywords WHERE type = \'find\' AND `use` = 1');
//count of messages
$count = 0;
//array so we dont duplicate messages
$personal_msgs = array();
while ($db->nextRecord()) {

	//now search personal messages
	$word = $db->getField('keyword');
	$id = $db->getField('id');
	$db2->query('SELECT * FROM message WHERE message_type_id = 2 AND sender_id != 0 AND message_text LIKE ' . $db2->escapeString('%'.$word.'%') . ' ORDER BY send_time DESC');
	while ($db2->nextRecord()) {

		//assume we arent skipping
		$skip = 'no';
		$bad = $db2->getField('message_text');
		$db3->query('SELECT * FROM mb_keywords WHERE assoc = '.$db3->escapeNumber($id).' AND type = \'ignore\' AND `use` = 1');
		while ($db3->nextRecord()) {
			$word2 = $db3->getField('keyword');
			$db4->query('SELECT '.$db->escapeString($bad).' LIKE ' . $db4->escapeString('%'.$word2.'%'));
			$db4->nextRecord();
			if ($db4->getField(0)) $skip = 'yes';
		}
		if ($skip == 'yes') continue;
		//first message only
		$msg_id = $db2->getField('message_id');
		if (in_array($msg_id,$personal_msgs))
			continue;
		$personal_msgs[] = $msg_id;
		//check if msg is okay or not
		$db3->query('SELECT * FROM mb_exceptions WHERE type = \'personal\' AND value = '.$db->escapeString($msg_id));
		if ($db3->getNumRows())
			continue;
		if ($count == 0) {
			//start table
			$PHP_OUTPUT.=('<a href=#button2>Goto Exception Button</a>');
			$PHP_OUTPUT.= create_table();
			$PHP_OUTPUT.=('<tr>');
			$PHP_OUTPUT.=('<th>Game ID</th>');
			$PHP_OUTPUT.=('<th>Sender ID</th>');
			$PHP_OUTPUT.=('<th>Bad text</th>');
			$PHP_OUTPUT.=('<th>Ignore</th>');
			$PHP_OUTPUT.=('</tr>');
		}

		//lets echo this message
		$PHP_OUTPUT.=('<tr>');
		$PHP_OUTPUT.=('<td class="center">' . $db2->getField('game_id') . '</td>');
		$PHP_OUTPUT.=('<td class="center">' . $db2->getField('sender_id') . '</td>');
		$array = array();
		$array[] = ucfirst($word);
		$array[] = strtoupper($word);
		$array[] = strtolower($word);
		$bad = str_replace($array, '<b><span class="red">'.$word.'</span></b>', $db->escapeString($bad));
		$PHP_OUTPUT.=('<td class="center">'.$bad.'</td>');
		$PHP_OUTPUT.=('<td class="center"><input type=checkbox name=personal[] value='.$msg_id.'></td>');
		$PHP_OUTPUT.=('</tr>');
		//update count
		$count += 1;

	}
}

if ($count == 0)
	$PHP_OUTPUT.=('No harmful messages found in personal messages</form><br />');
else {
	$PHP_OUTPUT.=('</table>');
	$PHP_OUTPUT.=('<a name=button2>');
	$PHP_OUTPUT.=create_submit('Add To Exception Table');
	$PHP_OUTPUT.=('</a></form>');
}
