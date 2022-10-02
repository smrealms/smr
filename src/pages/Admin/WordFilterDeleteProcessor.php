<?php declare(strict_types=1);

use Smr\Database;
use Smr\Request;

if (Request::has('word_ids')) {
	$db = Database::getInstance();
	$db->write('DELETE FROM word_filter WHERE word_id IN (' . $db->escapeArray(Request::getIntArray('word_ids')) . ')');
}

$container = Page::create('admin/word_filter.php');
$container->go();
