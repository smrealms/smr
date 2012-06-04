<?php

$container = array();
$container['url'] = 'skeleton.php';
$container['body'] = 'word_filter.php';

if(!isset($_REQUEST['Word']) || !isset($_REQUEST['WordReplacement'])) {
	$container['error'] = 1;	
	forward($container);
	exit;
}

$word = mysql_real_escape_string(strtoupper(trim($_REQUEST['Word'])));
$word_replacement = mysql_real_escape_string(strtoupper(trim($_REQUEST['WordReplacement'])));

if(empty($word) || empty($word_replacement)) {
	$container['error'] = 1;	
	forward($container);
	exit;	
}

$db = new SmrMySqlDatabase();

$db->query('SELECT word_id FROM word_filter WHERE word_value=\'' . $word . '\' LIMIT 1');

if($db->next_record()) {
	$container['error'] = 1;	
	forward($container);
	exit;
}

$db->query('INSERT INTO word_filter(word_value,word_replacement) VALUES (\'' . $word . '\',\'' . $word_replacement . '\')');

$container['error'] = 2;	
forward($container);
 
?>
