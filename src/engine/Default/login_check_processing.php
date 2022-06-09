<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$account = $session->getAccount();

if (!isset($var['CheckType']) || $var['CheckType'] == 'Validate') {
	// is account validated?
	if (!$account->isValidated()) {
		Page::create('validate.php')->go();
	} else {
		$var['CheckType'] = 'Announcements';
	}
}

$lastLogin = $account->getLastLogin();

$db = Smr\Database::getInstance();
if ($var['CheckType'] == 'Announcements') {
	$dbResult = $db->read('SELECT 1 FROM announcement WHERE time >= ' . $db->escapeNumber($lastLogin) . ' LIMIT 1');
	// do we have announcements?
	if ($dbResult->hasRecord()) {
		Page::create('announcements.php')->go();
	} else {
		$var['CheckType'] = 'Updates';
	}
}

if ($var['CheckType'] == 'Updates') {
	$dbResult = $db->read('SELECT 1 FROM version WHERE went_live >= ' . $db->escapeNumber($lastLogin) . ' LIMIT 1');
	// do we have updates?
	if ($dbResult->hasRecord()) {
		Page::create('changelog_view.php', ['Since' => $lastLogin])->go();
	}
}

Page::create('logged_in.php')->go();
