<?php declare(strict_types=1);

$template->assign('PageTopic', 'Word Filter');

if (isset($var['msg'])) {
	$template->assign('Message', $var['msg']);
}

$db->query('SELECT * FROM word_filter');
if ($db->getNumRows()) {
	$container = Page::create('word_filter_del.php');
	$template->assign('DelHREF', $container->href());

	$filteredWords = [];
	while ($db->nextRecord()) {
		$filteredWords[] = $db->getRow();
	}
	$template->assign('FilteredWords', $filteredWords);
}

$container = Page::create('word_filter_add.php');
$template->assign('AddHREF', $container->href());
