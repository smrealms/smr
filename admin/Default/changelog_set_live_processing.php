<?php

$db->query('UPDATE version
			SET went_live = ' . TIME . '
			WHERE version_id = ' . $var['version_id']
		   );

$db->query('SELECT * FROM version WHERE version_id = ' . $var['version_id']);
$db->nextRecord();
$db->query('INSERT IGNORE INTO version (version_id, major_version, minor_version, patch_level, went_live) VALUES
			('.$db->getInt('version_id').','.$db->getInt('major_version').','.$db->getInt('minor_version').','.($db->getInt('patch_level')+1).',0);');

forward(create_container('skeleton.php', 'changelog.php'));

?>