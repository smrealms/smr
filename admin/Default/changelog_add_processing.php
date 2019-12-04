<?php declare(strict_types=1);

$change_title = $_REQUEST['change_title'];
$change_message = $_REQUEST['change_message'];
$affected_db = $_REQUEST['affected_db'];

$container = create_container('skeleton.php', 'changelog.php');

if ($_REQUEST['action'] == 'Preview') {
	$container['change_title'] = $change_title;
	$container['change_message'] = $change_message;
	$container['affected_db'] = $affected_db;
	forward($container);
}

$db->lockTable('changelog');

$db->query('SELECT MAX(changelog_id)
			FROM changelog
			WHERE version_id = ' . $db->escapeNumber($var['version_id'])
		   );
if ($db->nextRecord()) {
	$changelog_id = $db->getField('MAX(changelog_id)') + 1;
} else {
	$changelog_id = 1;
}

$db->query('INSERT INTO changelog
			(version_id, changelog_id, change_title, change_message, affected_db)
			VALUES (' . $db->escapeNumber($var['version_id']) . ', ' . $db->escapeNumber($changelog_id) . ', ' . $db->escapeString($change_title) . ', ' . $db->escapeString($change_message) . ', ' . $db->escapeString($affected_db) . ')');

$db->unlock();

forward($container);
