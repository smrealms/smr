<?php

$container = create_container('skeleton.php', 'word_filter.php');

if(!isset($_REQUEST['word_ids']) || !is_array($_REQUEST['word_ids'])) {
	$container['error'] = 2;	
	forward($container);
	exit;
}

foreach($_REQUEST['word_ids'] as $word_id) {
	if(!is_numeric($word_id)) {
		$container['error'] = 5;	
		forward($container);
		exit;
	}
	else {
		$word_ids[] = $word_id;
	}
}

$db->query('DELETE FROM word_filter WHERE word_id IN (' . $db->escapeArray($word_ids) . ')');
forward($container);
 
?>
