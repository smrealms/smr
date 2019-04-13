<?php

if (!empty($_REQUEST['word_ids'])) {
	$db->query('DELETE FROM word_filter WHERE word_id IN (' . $db->escapeArray($_REQUEST['word_ids']) . ')');
}

$container = create_container('skeleton.php', 'word_filter.php');
forward($container);
