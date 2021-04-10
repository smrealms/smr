<?php declare(strict_types=1);

$template = Smr\Template::getInstance();

$processingHREF = Page::create('game_status_processing.php')->href();
$template->assign('ProcessingHREF', $processingHREF);

$db = Smr\Database::getInstance();
$db->query('SELECT * FROM game_disable');
if (!$db->getNumRows()) {
	$template->assign('PageTopic', 'Close Server');
	$template->assign('ServerIsOpen', true);
} else {
	$template->assign('PageTopic', 'Open Server');
	$template->assign('ServerIsOpen', false);
}
