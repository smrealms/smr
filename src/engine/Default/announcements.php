<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$db = Smr\Database::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();

$template->assign('PageTopic', 'Announcements');

if (!isset($var['view_all'])) {
	$session = Smr\Session::getInstance();
	$account = $session->getAccount();

	$dbResult = $db->read('SELECT time, msg
				FROM announcement
				WHERE time > ' . $db->escapeNumber($account->getLastLogin()) . '
				ORDER BY time DESC');
} else {
	$dbResult = $db->read('SELECT time, msg
				FROM announcement
				ORDER BY time DESC');
}

$announcements = [];
foreach ($dbResult->records() as $dbRecord) {
	$announcements[] = [
		'Time' => $dbRecord->getInt('time'),
		'Msg' => htmlentities($dbRecord->getString('msg')),
	];
}
$template->assign('Announcements', $announcements);

$container = Page::create('login_check_processing.php');
$container['CheckType'] = 'Updates';
$template->assign('ContinueHREF', $container->href());
