<?php declare(strict_types=1);

$var = Smr\Session::getInstance()->getCurrentVar();

$change_title = Smr\Request::get('change_title');
$change_message = Smr\Request::get('change_message');
$affected_db = Smr\Request::get('affected_db');

$container = Page::create('admin/changelog.php');

if (Smr\Request::get('action') == 'Preview') {
	$container['change_title'] = $change_title;
	$container['change_message'] = $change_message;
	$container['affected_db'] = $affected_db;
	$container->go();
}

$db = Smr\Database::getInstance();
$db->lockTable('changelog');

$dbResult = $db->read('SELECT IFNULL(MAX(changelog_id)+1, 0) AS next_changelog_id
			FROM changelog
			WHERE version_id = ' . $db->escapeNumber($var['version_id']));
$changelog_id = $dbResult->record()->getInt('next_changelog_id');

$db->insert('changelog', [
	'version_id' => $db->escapeNumber($var['version_id']),
	'changelog_id' => $db->escapeNumber($changelog_id),
	'change_title' => $db->escapeString($change_title),
	'change_message' => $db->escapeString($change_message),
	'affected_db' => $db->escapeString($affected_db),
]);
$db->unlock();

$container->go();
