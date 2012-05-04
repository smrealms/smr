<?php

$container = create_container('skeleton.php', 'word_filter.php');

if(!isset($_REQUEST['Word']) || !isset($_REQUEST['WordReplacement'])) {
	$container['error'] = 1;
	forward($container);
	exit;
}

$word = strtoupper(trim($_REQUEST['Word']));
$word_replacement = strtoupper(trim($_REQUEST['WordReplacement']));

if(empty($word) || empty($word_replacement)) {
	$container['error'] = 1;
	forward($container);
	exit;
}

$db = new SmrMySqlDatabase();

$db->query('SELECT word_id FROM word_filter WHERE word_value=' . $db->escapeString($word) . ' LIMIT 1');

if($db->nextRecord()) {
	$container['error'] = 1;
	forward($container);
	exit;
}

$db->query('INSERT INTO word_filter(word_value,word_replacement) VALUES (' . $db->escapeString($word) . ',' . $db->escapeString($word_replacement) . ')');

$container['error'] = 2;
forward($container);

?>
