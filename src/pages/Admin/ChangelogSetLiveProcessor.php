<?php declare(strict_types=1);

use Smr\Database;
use Smr\Epoch;

		$var = Smr\Session::getInstance()->getCurrentVar();

		$db = Database::getInstance();
		$db->write('UPDATE version
					SET went_live = ' . $db->escapeNumber(Epoch::time()) . '
					WHERE version_id = ' . $db->escapeNumber($var['version_id']));

		// Initialize the next version (since the version set live is not always the
		// last one, we INSERT IGNORE to skip this step in this case).
		$dbResult = $db->read('SELECT * FROM version WHERE version_id = ' . $db->escapeNumber($var['version_id']));
		$dbRecord = $dbResult->record();
		$versionID = $dbRecord->getInt('version_id') + 1;
		$major = $dbRecord->getInt('major_version');
		$minor = $dbRecord->getInt('minor_version');
		$patch = $dbRecord->getInt('patch_level') + 1;
		$db->write('INSERT IGNORE INTO version (version_id, major_version, minor_version, patch_level, went_live) VALUES
					(' . $db->escapeNumber($versionID) . ',' . $db->escapeNumber($major) . ',' . $db->escapeNumber($minor) . ',' . $db->escapeNumber($patch) . ',0);');

		Page::create('admin/changelog.php')->go();
