<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();

$template->assign('PageTopic', 'Preferences');

if (isset($var['reason'])) {
	$template->assign('Reason', $var['reason']);
}

$template->assign('PreferencesFormHREF', Page::create('preferences_processing.php', '')->href());

$template->assign('PreferencesConfirmFormHREF', Page::create('skeleton.php', 'preferences_confirm.php')->href());

$template->assign('ChatSharingHREF', Page::create('skeleton.php', 'chat_sharing.php')->href());

$transferAccounts = array();
$db = Smr\Database::getInstance();
$dbResult = $db->read('SELECT account_id,hof_name FROM account WHERE validated = ' . $db->escapeBoolean(true) . ' ORDER BY hof_name');
foreach ($dbResult->records() as $dbRecord) {
	$transferAccounts[$dbRecord->getInt('account_id')] = htmlspecialchars($dbRecord->getString('hof_name'));
}
$template->assign('TransferAccounts', $transferAccounts);
