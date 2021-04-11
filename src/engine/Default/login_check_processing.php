<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$account = $session->getAccount();

if (!isset($var['CheckType']) || $var['CheckType'] == 'Validate') {
	// is account validated?
	if (!$account->isValidated()) {
		Page::create('skeleton.php', 'validate.php')->go();
	} else {
		$var['CheckType'] = 'Announcements';
	}
}

$lastLogin = $account->getLastLogin();

$db = Smr\Database::getInstance();
if ($var['CheckType'] == 'Announcements') {
	$db->query('SELECT 1 FROM announcement WHERE time >= ' . $db->escapeNumber($lastLogin) . ' LIMIT 1');
	// do we have announcements?
	if ($db->nextRecord()) {
		Page::create('skeleton.php', 'announcements.php')->go();
	} else {
		$var['CheckType'] = 'Updates';
	}
}

if ($var['CheckType'] == 'Updates') {
	$db->query('SELECT 1 FROM version WHERE went_live >= ' . $db->escapeNumber($lastLogin) . ' LIMIT 1');
	// do we have updates?
	if ($db->nextRecord()) {
		Page::create('skeleton.php', 'changelog_view.php', array('Since' => $lastLogin))->go();
	}
}

Page::create('logged_in.php')->go();
