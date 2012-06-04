<?

//script to search alliance message boards and personal messages for cheats/loopholes
//first get our keywords
$db->query('SELECT * FROM mb_keywords WHERE type = \'find\' AND `use` = 1');
$db2 = new SMR_DB();
$db3 = new SMR_DB();
$db4 = new SMR_DB();
if (isset($var['msg'])) $PHP_OUTPUT.=($var['msg'] . '<br><br>');

$container = array();
$container['url'] = 'keyword_processing.php';
$container['type'] = 'alliance';
$PHP_OUTPUT.=create_echo_form($container);
//count of messages
$count = 0;
//array for mb so we dont duplicate
$mb_msgs = array();
while ($db->next_record()) {
	
	//search every message on webboards for each word first
	$id = $db->f('id');
	$word = $db->f('keyword');
	
	$db2->query('SELECT * FROM alliance_thread WHERE sender_id != 0 AND text LIKE \'%'.$word.'%\' ORDER BY time DESC');
	while ($db2->next_record()) {
		//assume we arent skipping
		$skip = 'no';
		$bad = $db2->f('text');
		$db3->query('SELECT * FROM mb_keywords WHERE assoc = '.$id.' AND type = \'ignore\' AND `use` = 1');
		while ($db3->next_record()) {
			$word2 = $db3->f('keyword');
			$sql = 'SELECT '.$db->escapeString($bad).' LIKE \'%'.$word2.'%\'';
			$db4->query($sql);
			$db4->next_record();
			if ($db4->f(0)) $skip = 'yes';
		}
		if ($skip == 'yes') continue;
		//get info
		$game_id = $db2->f('game_id');
		$alliance_id = $db2->f('alliance_id');
		$thread_id = $db2->f('thread_id');
		$reply_id = $db2->f('reply_id');
		//put in an array
		$array_filler = $game_id.','.$alliance_id.','.$thread_id.','.$reply_id;
		//check if its already been done
		if (in_array($array_filler,$mb_msgs))
			continue;
		else
			$mb_msgs[] = $array_filler;
		//check if msg is okay or not
		$db3->query('SELECT * FROM mb_exceptions WHERE type = \'alliance\' AND value = '.$db->escapeString($array_filler));
		if ($db3->nf())
			continue;
		//only if this is first message found
		if ($count == 0) {
			//start table
			$PHP_OUTPUT.=('<a href=#button1>Goto Exception Button/Personal Messages Start</a>');
			echo_table();
			$PHP_OUTPUT.=('<tr>');
			$PHP_OUTPUT.=('<th align=center>Game ID</th>');
			$PHP_OUTPUT.=('<th align=center>Alliance ID</th>');
			$PHP_OUTPUT.=('<th align=center>Thread ID</th>');
			$PHP_OUTPUT.=('<th align=center>Reply ID</th>');
			$PHP_OUTPUT.=('<th align=center>Sender ID</th>');
			$PHP_OUTPUT.=('<th align=center>Bad text</th>');
			$PHP_OUTPUT.=('<th align=center>Ignore</th>');
			$PHP_OUTPUT.=('</tr>');
		}
		
		//lets echo this message
		$PHP_OUTPUT.=('<tr>');
		$PHP_OUTPUT.=('<td align=center>'.$game_id.'</td>');
		$PHP_OUTPUT.=('<td align=center>'.$alliance_id.'</td>');
		$PHP_OUTPUT.=('<td align=center>'.$thread_id.'</td>');
		$PHP_OUTPUT.=('<td align=center>'.$reply_id.'</td>');
		//make sure we check for Word, WORD, and word...after phpv5 use str_ireplace
		$array = array();
		$array[] = ucfirst($word);
		$array[] = strtoupper($word);
		$array[] = strtolower($word);
		$bad = str_replace($array, '<b><font color=red>'.$word.'</font></b>', $db->escapeString($bad));
		$PHP_OUTPUT.=('<td align=center>' . $db2->f('sender_id') . '</td>');
		$PHP_OUTPUT.=('<td align=center>'.$bad.'</td>');
		$PHP_OUTPUT.=('<td align=center><input type=checkbox name=alliance[] value='.$array_filler.'></td>');
		$PHP_OUTPUT.=('</tr>');
		//update count
		$count += 1;
	
	}
}

if ($count == 0)
	$PHP_OUTPUT.=('No harmful messages found on alliance webboards<br></form>');
else {
	$PHP_OUTPUT.=('</table><br>');
	$PHP_OUTPUT.=('<a name=button1>');
	$PHP_OUTPUT.=create_submit('Add To Exception Table');
	$PHP_OUTPUT.=('</a></form>');
}
$PHP_OUTPUT.=('<br><br>');

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
while ($db->next_record()) {
	
	//now search personal messages
	$word = $db->f('keyword');
	$id = $db->f('id');
	$db2->query('SELECT * FROM message WHERE message_type_id = 2 AND sender_id != 0 AND message_text LIKE \'%'.$word.'%\' ORDER BY send_time DESC');
	while ($db2->next_record()) {
		
		//assume we arent skipping
		$skip = 'no';
		$bad = $db2->f('message_text');
		$db3->query('SELECT * FROM mb_keywords WHERE assoc = '.$id.' AND type = \'ignore\' AND `use` = 1');
		while ($db3->next_record()) {
			$word2 = $db3->f('keyword');
			$sql = 'SELECT '.$db->escapeString($bad).' LIKE \'%'.$word2.'%\'';
			$db4->query($sql);
			$db4->next_record();
			if ($db4->f(0)) $skip = 'yes';
		}
		if ($skip == 'yes') continue;
		//first message only
		$msg_id = $db2->f('message_id');
		if (in_array($msg_id,$personal_msgs))
			continue;
		$personal_msgs[] = $msg_id;
		//check if msg is okay or not
		$db3->query('SELECT * FROM mb_exceptions WHERE type = \'personal\' AND value = '.$db->escapeString($msg_id));
		if ($db3->nf())
			continue;
		if ($count == 0) {
			//start table
			$PHP_OUTPUT.=('<a href=#button2>Goto Exception Button</a>');
			echo_table();
			$PHP_OUTPUT.=('<tr>');
			$PHP_OUTPUT.=('<th align=center>Game ID</th>');
			$PHP_OUTPUT.=('<th align=center>Sender ID</th>');
			$PHP_OUTPUT.=('<th align=center>Bad text</th>');
			$PHP_OUTPUT.=('<th align=center>Ignore</th>');
			$PHP_OUTPUT.=('</tr>');
		}
		
		//lets echo this message
		$PHP_OUTPUT.=('<tr>');
		$PHP_OUTPUT.=('<td align=center>' . $db2->f('game_id') . '</td>');
		$PHP_OUTPUT.=('<td align=center>' . $db2->f('sender_id') . '</td>');
		$array = array();
		$array[] = ucfirst($word);
		$array[] = strtoupper($word);
		$array[] = strtolower($word);
		$bad = str_replace($array, '<b><font color=red>'.$word.'</font></b>', $db->escapeString($bad));
		$PHP_OUTPUT.=('<td align=center>'.$bad.'</td>');
		$PHP_OUTPUT.=('<td align=center><input type=checkbox name=personal[] value='.$msg_id.'></td>');
		$PHP_OUTPUT.=('</tr>');
		//update count
		$count += 1;
		
	}
}

if ($count == 0)
	$PHP_OUTPUT.=('No harmful messages found in personal messages</form><br>');
else {
	$PHP_OUTPUT.=('</table>');
	$PHP_OUTPUT.=('<a name=button2>');
	$PHP_OUTPUT.=create_submit('Add To Exception Table');
	$PHP_OUTPUT.=('</a></form>');
}

?>