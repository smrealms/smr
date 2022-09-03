<?php declare(strict_types=1);

use Smr\Database;

$template = Smr\Template::getInstance();

$processingHREF = Page::create('admin/game_status_processing.php')->href();
$template->assign('ProcessingHREF', $processingHREF);

$db = Database::getInstance();
$dbResult = $db->read('SELECT 1 FROM game_disable');
if (!$dbResult->hasRecord()) {
	$template->assign('PageTopic', 'Close Server');
	$template->assign('ServerIsOpen', true);
} else {
	$template->assign('PageTopic', 'Open Server');
	$template->assign('ServerIsOpen', false);
}
