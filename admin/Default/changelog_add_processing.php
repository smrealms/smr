<?php

if (empty($_REQUEST['change_message']))
	create_error('The message can\'t be empty!');

$db->lockTable('changelog');

$db->query('SELECT MAX(changelog_id)
			FROM changelog
			WHERE version_id = ' . $db->escapeNumber($var['version_id'])
		   );
if ($db->nextRecord())
	$changelog_id = $db->getField('MAX(changelog_id)') + 1;
else
	$changelog_id = 1;

$change_title	= $_REQUEST['change_title'];
$change_message	= nl2br($_REQUEST['change_message']);
$affected_db	= nl2br($_REQUEST['affected_db']);

$db->query('INSERT INTO changelog
			(version_id, changelog_id, change_title, change_message, affected_db)
			VALUES (' . $db->escapeNumber($var['version_id']) . ', '.$db->escapeNumber($changelog_id).', '.$db->escapeString($change_title).', '.$db->escapeString($change_message).', '.$db->escapeString($affected_db).')');

$db->unlock();

forward(create_container('skeleton.php', 'changelog.php'));

?>