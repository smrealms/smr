<?php declare(strict_types=1);

$template->assign('PageTopic', 'Change Log');

if (isset($var['Since'])) {
	$container = create_container('logged_in.php');
	$template->assign('ContinueHREF', SmrSession::getNewHREF($container));
}

$db2 = new SmrMySqlDatabase();

$db->query('SELECT *
			FROM version
			WHERE went_live > ' . (isset($var['Since']) ? $db->escapeNumber($var['Since']) : '0') . '
			ORDER BY version_id DESC');

$versions = [];
while ($db->nextRecord()) {
	$version_id = $db->getInt('version_id');
	$version = $db->getInt('major_version') . '.' . $db->getInt('minor_version') . '.' . $db->getInt('patch_level');
	$went_live = $db->getInt('went_live');

	// get human readable format for date
	if ($went_live > 0) {
		$went_live = date(DATE_FULL_SHORT, $went_live);
	} else {
		$went_live = 'never';
	}

	$db2->query('SELECT *
				FROM changelog
				WHERE version_id = ' . $db2->escapeNumber($version_id) . '
				ORDER BY changelog_id');
	$changes = [];
	while ($db2->nextRecord()) {
		$changes[] = [
			'title' => $db2->getField('change_title'),
			'message' => $db2->getField('change_message'),
		];
	}

	$versions[] = [
		'version' => $version,
		'went_live' => $went_live,
		'changes' => $changes,
	];
}
$template->assign('Versions', $versions);
