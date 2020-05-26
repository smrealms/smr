<?php declare(strict_types=1);

$db->query('UPDATE version
			SET went_live = ' . $db->escapeNumber(TIME) . '
			WHERE version_id = ' . $db->escapeNumber($var['version_id'])
		   );

// Initialize the next version (since the version set live is not always the
// last one, we INSERT IGNORE to skip this step in this case).
$db->query('SELECT * FROM version WHERE version_id = ' . $db->escapeNumber($var['version_id']));
$db->requireRecord();
$versionID = $db->getInt('version_id') + 1;
$major = $db->getInt('major_version');
$minor = $db->getInt('minor_version');
$patch = $db->getInt('patch_level') + 1;
$db->query('INSERT IGNORE INTO version (version_id, major_version, minor_version, patch_level, went_live) VALUES
			('.$db->escapeNumber($versionID) . ',' . $db->escapeNumber($major) . ',' . $db->escapeNumber($minor) . ',' . $db->escapeNumber($patch) . ',0);');

forward(create_container('skeleton.php', 'changelog.php'));
