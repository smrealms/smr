<?php declare(strict_types=1);

$word = strtoupper(trim($_REQUEST['Word']));
$word_replacement = strtoupper(trim($_REQUEST['WordReplacement']));

$container = create_container('skeleton.php', 'word_filter.php');

$db->query('SELECT word_id FROM word_filter WHERE word_value=' . $db->escapeString($word) . ' LIMIT 1');
if ($db->nextRecord()) {
	$container['msg'] = '<span class="red bold">ERROR: </span>This word is already filtered!';
	forward($container);
}

$db->query('INSERT INTO word_filter(word_value,word_replacement) VALUES (' . $db->escapeString($word) . ',' . $db->escapeString($word_replacement) . ')');

$container['msg'] = '<span class="yellow">' . $word . '</span> will now be replaced with <span class="yellow">' . $word_replacement . '</span>.';
forward($container);
