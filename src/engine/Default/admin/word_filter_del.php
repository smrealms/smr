<?php declare(strict_types=1);

if (Smr\Request::has('word_ids')) {
	$db = Smr\Database::getInstance();
	$db->write('DELETE FROM word_filter WHERE word_id IN (' . $db->escapeArray(Smr\Request::getIntArray('word_ids')) . ')');
}

$container = Page::create('skeleton.php', 'admin/word_filter.php');
$container->go();
