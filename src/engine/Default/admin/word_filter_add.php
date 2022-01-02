<?php declare(strict_types=1);

$word = strtoupper(trim(Smr\Request::get('Word')));
$word_replacement = strtoupper(trim(Smr\Request::get('WordReplacement')));

$container = Page::create('skeleton.php', 'admin/word_filter.php');

$db = Smr\Database::getInstance();
$dbResult = $db->read('SELECT 1 FROM word_filter WHERE word_value=' . $db->escapeString($word) . ' LIMIT 1');
if ($dbResult->hasRecord()) {
	$container['msg'] = '<span class="red bold">ERROR: </span>This word is already filtered!';
	$container->go();
}

$db->write('INSERT INTO word_filter(word_value,word_replacement) VALUES (' . $db->escapeString($word) . ',' . $db->escapeString($word_replacement) . ')');

$container['msg'] = '<span class="yellow">' . $word . '</span> will now be replaced with <span class="yellow">' . $word_replacement . '</span>.';
$container->go();
