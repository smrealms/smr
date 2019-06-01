<?php
if (!isset($var['CheckType']) || $var['CheckType'] == 'Validate') {
	// is account validated?
	if (!$account->isValidated()) {
		forward(create_container('skeleton.php', 'validate.php'));
	}
	else {
		$var['CheckType'] = 'Announcements';
	}
}

$lastLogin = $account->getLastLogin();

$db = new SmrMySqlDatabase();
if ($var['CheckType'] == 'Announcements') {
	$db->query('SELECT 1 FROM announcement WHERE time >= ' . $db->escapeNumber($lastLogin) . ' LIMIT 1');
	// do we have announcements?
	if ($db->nextRecord()) {
		forward(create_container('skeleton.php', 'announcements.php'));
	}
	else {
		$var['CheckType'] = 'Updates';
	}
}

if ($var['CheckType'] == 'Updates') {
	$db->query('SELECT 1 FROM version WHERE went_live >= ' . $db->escapeNumber($lastLogin) . ' LIMIT 1');
	// do we have updates?
	if ($db->nextRecord()) {
		forward(create_container('skeleton.php', 'changelog_view.php', array('Since' => $lastLogin)));
	}
}

forward(create_container('logged_in.php'));
