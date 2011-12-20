<?php

$template->assign('PageTopic','Change Log');

$db2 = new SmrMySqlDatabase();

$db->query('SELECT *
			FROM version
			WHERE version_id <= ' . $db->escapeNumber($var['version_id']) . '
			ORDER BY version_id DESC');
while ($db->nextRecord()) {
	$version_id = $db->getInt('version_id');
	$version = $db->getInt('major_version') . '.' . $db->getInt('minor_version') . '.' . $db->getInt('patch_level');
	$went_live = $db->getInt('went_live');

	// get human readable format for date
	if ($went_live > 0) {
		$went_live = date(DATE_FULL_SHORT, $went_live);
	}
	else {
		$went_live = 'never';
	}

	$PHP_OUTPUT.=('<b><small>'.$version.' ('.$went_live.'):</small></b>');

	$PHP_OUTPUT.=('<ul>');

	$db2->query('SELECT *
				FROM changelog
				WHERE version_id = ' . $db2->escapeNumber($version_id) . '
				ORDER BY changelog_id');
	while ($db2->nextRecord()) {
		$PHP_OUTPUT.=('<li>' . $db2->getField('change_title') . '<br /><small>' . $db2->getField('change_message') . '</small></li>');
	}

	$PHP_OUTPUT.=('</ul><br />');
}

?>