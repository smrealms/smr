<?php

if (empty($_POST['change_message']))
	create_error('The message can\'t be empty!');

$db->lockTable('changelog');

$db->query('SELECT MAX(changelog_id)
			FROM changelog
			WHERE version_id = ' . $var['version_id']
		   );
if ($db->nextRecord())
	$changelog_id = $db->getField('MAX(changelog_id)') + 1;
else
	$changelog_id = 1;

$change_title	= mysql_escape_string($_POST['change_title']);
$change_message	= mysql_escape_string(nl2br($_POST['change_message']));
$affected_db	= mysql_escape_string(nl2br($_POST['affected_db']));

$db->query('INSERT INTO changelog
			(version_id, changelog_id, change_title, change_message, affected_db)
			VALUES (' . $var['version_id'] . ', '.$changelog_id.', '.$db->escapeString($change_title).', '.$db->escapeString($change_message).', '.$db->escapeString($affected_db).')');

$db->unlock();

forward(create_container('skeleton.php', 'changelog.php'));

?>