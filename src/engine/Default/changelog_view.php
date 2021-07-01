<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$account = $session->getAccount();

$template->assign('PageTopic', 'Change Log');

if (isset($var['Since'])) {
	$container = Page::create('logged_in.php');
	$template->assign('ContinueHREF', $container->href());
}

$db = Smr\Database::getInstance();

$dbResult = $db->read('SELECT *
			FROM version
			WHERE went_live > ' . (isset($var['Since']) ? $db->escapeNumber($var['Since']) : '0') . '
			ORDER BY version_id DESC');

$versions = [];
foreach ($dbResult->records() as $dbRecord) {
	$version_id = $dbRecord->getInt('version_id');
	$version = $dbRecord->getInt('major_version') . '.' . $dbRecord->getInt('minor_version') . '.' . $dbRecord->getInt('patch_level');
	$went_live = $dbRecord->getInt('went_live');

	// get human readable format for date
	if ($went_live > 0) {
		$went_live = date($account->getDateTimeFormat(), $went_live);
	} else {
		$went_live = 'never';
	}

	$dbResult2 = $db->read('SELECT *
				FROM changelog
				WHERE version_id = ' . $db->escapeNumber($version_id) . '
				ORDER BY changelog_id');
	$changes = [];
	foreach ($dbResult2->records() as $dbRecord2) {
		$changes[] = [
			'title' => $dbRecord2->getField('change_title'),
			'message' => $dbRecord2->getField('change_message'),
		];
	}

	$versions[] = [
		'version' => $version,
		'went_live' => $went_live,
		'changes' => $changes,
	];
}
$template->assign('Versions', $versions);
