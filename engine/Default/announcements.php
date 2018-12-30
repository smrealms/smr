<?php

$template->assign('PageTopic','Announcements');

if (!isset($var['view_all'])) {
	$db->query('SELECT time, msg
				FROM announcement
				WHERE time > ' . $db->escapeNumber($account->getLastLogin()) . '
				ORDER BY time DESC');
}
else {
	$db->query('SELECT time, msg
				FROM announcement
				ORDER BY time DESC');
}

$announcements = [];
while ($db->nextRecord()) {
	$announcements[] = ['Time' => $db->getInt('time'),
	                    'Msg' => $db->getField('msg')];
}
$template->assign('Announcements', $announcements);

$container = create_container('login_check_processing.php');
$container['CheckType'] = 'Updates';
$template->assign('ContinueHREF', SmrSession::getNewHREF($container));
