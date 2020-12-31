<?php declare(strict_types=1);

$template->assign('PageTopic', 'Word Filter');

if (isset($var['msg'])) {
	$template->assign('Message', $var['msg']);
}

$db->query('SELECT * FROM word_filter');
if ($db->getNumRows()) {
	$container = create_container('word_filter_del.php');
	$template->assign('DelHREF', SmrSession::getNewHREF($container));

	$filteredWords = [];
	while ($db->nextRecord()) {
		$filteredWords[] = $db->getRow();
	}
	$template->assign('FilteredWords', $filteredWords);
}

$container = create_container('word_filter_add.php');
$template->assign('AddHREF', SmrSession::getNewHREF($container));
