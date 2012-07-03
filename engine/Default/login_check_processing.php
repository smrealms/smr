<?php
if(!isset($var['CheckType']) || $var['CheckType'] == 'Validate') {
	// is account validated?
	if (!$account->isValidated()) {
		forward(create_container('skeleton.php', 'validate.php'));
	}
	else {
		$var['CheckType'] = 'Announcements';
	}
}
$db = new SmrMySqlDatabase();
if($var['CheckType'] == 'Announcements') {
	$db->query('SELECT 1 FROM account JOIN announcement ON last_login < time
				WHERE account_id = ' . $db->escapeNumber(SmrSession::$account_id) . ' LIMIT 1');
	// do we have announcements?
	if ($db->nextRecord()) {
		forward(create_container('skeleton.php', 'announcements.php'));
	}
	else {
		$var['CheckType'] = 'Updates';
	}
}

if($var['CheckType'] == 'Updates') {
	$db->query('SELECT last_login FROM account JOIN version ON last_login < went_live
				WHERE account_id = ' . $db->escapeNumber(SmrSession::$account_id) . ' LIMIT 1');
	// do we have updates?
	if ($db->nextRecord()) {
		forward(create_container('skeleton.php', 'changelog_view.php', array('Since' => $db->getInt('last_login'))));
	}
}

forward(create_container('logged_in.php'));
?>