<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();

$template->assign('PageTopic', 'Database Cleanup');

function bytesToMB(int $bytes): string {
	return round($bytes / (1024 * 1024), 1) . ' MB';
}

$db = Smr\Database::getInstance();
$template->assign('DbSizeMB', bytesToMB($db->getDbBytes()));

if (isset($var['results'])) {
	// Display the results
	$template->assign('Results', $var['results']);
	$template->assign('DiffMB', bytesToMB($var['diffBytes']));
	$template->assign('Action', $var['action']);
	$template->assign('EndedGames', $var['endedGames']);
	$container = Page::create('skeleton.php', 'admin/db_cleanup.php');
	$template->assign('BackHREF', $container->href());
} else {
	// Create processing links
	$container = Page::create('admin/db_cleanup_processing.php');
	$container['action'] = 'delete';
	$template->assign('DeleteHREF', $container->href());
	$container['action'] = 'preview';
	$template->assign('PreviewHREF', $container->href());
}
