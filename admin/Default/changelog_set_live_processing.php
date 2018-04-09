<?php

$db->query('UPDATE version
			SET went_live = ' . $db->escapeNumber(TIME) . '
			WHERE version_id = ' . $db->escapeNumber($var['version_id'])
		   );

$db->query('SELECT * FROM version WHERE version_id = ' . $db->escapeNumber($var['version_id']));
$db->nextRecord();
$db->query('INSERT IGNORE INTO version (version_id, major_version, minor_version, patch_level, went_live) VALUES
			('.$db->escapeNumber($db->getInt('version_id')+1).','.$db->escapeNumber($db->getInt('major_version')).','.$db->escapeNumber($db->getInt('minor_version')).','.$db->escapeNumber($db->getInt('patch_level')+1).',0);');

forward(create_container('skeleton.php', 'changelog.php'));
