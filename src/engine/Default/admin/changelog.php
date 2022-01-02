<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$account = $session->getAccount();

$template->assign('PageTopic', 'Change Log');

$template->assign('ChangeTitle', $var['change_title'] ?? '');
$template->assign('ChangeMessage', $var['change_message'] ?? '');
$template->assign('AffectedDb', $var['affected_db'] ?? '');

$first_entry = true;
$link_set_live = true;

$db = Smr\Database::getInstance();
$dbResult = $db->read('SELECT * FROM version ORDER BY version_id DESC');

$versions = [];
foreach ($dbResult->records() as $dbRecord) {
	$version_id = $dbRecord->getInt('version_id');
	$version = $dbRecord->getInt('major_version') . '.' . $dbRecord->getInt('minor_version') . '.' . $dbRecord->getInt('patch_level');
	$went_live = $dbRecord->getInt('went_live');
	if ($went_live > 0) {
		// from this point on we don't create links to set a version to live
		$link_set_live = false;

		// get human readable format for date
		$went_live = date($account->getDateTimeFormat(), $went_live);
	} else {
		if ($link_set_live) {
			$container = Page::create('admin/changelog_set_live_processing.php');
			$container['version_id'] = $version_id;
			$went_live = create_link($container, 'never');
		} else {
			$went_live = 'never';
		}
	}

	$dbResult2 = $db->read('SELECT *
				FROM changelog
				WHERE version_id = '.$db->escapeNumber($version_id) . '
				ORDER BY changelog_id');
	$changes = [];
	foreach ($dbResult2->records() as $dbRecord2) {
		$changes[] = [
			'title' => $dbRecord2->getField('change_title'),
			'message' => $dbRecord2->getField('change_message'),
		];
	}

	$version = [
		'version' => $version,
		'went_live' => $went_live,
		'changes' => $changes,
	];

	if ($first_entry) {
		$first_entry = false;
		$container = Page::create('admin/changelog_add_processing.php');
		$container['version_id'] = $version_id;
		$template->assign('AddHREF', $container->href());

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
