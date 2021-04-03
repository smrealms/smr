<?php declare(strict_types=1);

if (Request::has('word_ids')) {
	$db->query('DELETE FROM word_filter WHERE word_id IN (' . $db->escapeArray(Request::getIntArray('word_ids')) . ')');
}

$container = Page::create('skeleton.php', 'word_filter.php');
$container->go();
