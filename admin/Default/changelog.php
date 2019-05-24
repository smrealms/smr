<?php

$template->assign('PageTopic','Change Log');

$template->assign('ChangeTitle', $var['change_title'] ?? '');
$template->assign('ChangeMessage', $var['change_message'] ?? '');
$template->assign('AffectedDb', $var['affected_db'] ?? '');

$db2 = new SmrMySqlDatabase();
$first_entry = true;
$link_set_live = true;

$db->query('SELECT * FROM version ORDER BY version_id DESC');

while ($db->nextRecord()) {
	$version_id = $db->getInt('version_id');
	$version = $db->getInt('major_version') . '.' . $db->getInt('minor_version') . '.' . $db->getInt('patch_level');
	$went_live = $db->getInt('went_live');
	if ($went_live > 0) {
		// from this point on we don't create links to set a version to live
		$link_set_live = false;

		// get human readable format for date
		$went_live = date(DATE_FULL_SHORT, $went_live);
	} else {
		if ($link_set_live) {
			$container = create_container('changelog_set_live_processing.php');
			$container['version_id'] = $version_id;
			$went_live = create_link($container, 'never');
		} else {
			$went_live = 'never';
		}
	}

	$db2->query('SELECT *
				FROM changelog
				WHERE version_id = '.$db->escapeNumber($version_id).'
				ORDER BY changelog_id');
	$changes = [];
	while ($db2->nextRecord()) {
		$changes[] = [
			'title' => $db2->getField('change_title'),
			'message' => $db2->getField('change_message'),
		];
	}

	$version = [
		'version' => $version,
		'went_live' => $went_live,
		'changes' => $changes,
	];

	if ($first_entry) {
		$first_entry = false;
		$container = create_container('changelog_add_processing.php');
		$container['version_id'] = $version_id;
		$template->assign('AddHREF', SmrSession::getNewHREF($container));

		if (isset($var['change_title'])) {
			$version['changes'][] = [
				'title' => '<span class="red">PREVIEW: </span>' . $var['change_title'],
				'message' => $var['change_message'],
			];
		}
		$template->assign('FirstVersion', $version);
	} else {
		$versions[] = $version;
	}
}
$template->assign('Versions', $versions);
