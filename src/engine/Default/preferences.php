<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();

$template->assign('PageTopic', 'Preferences');

if ($session->hasGame()) {
	$template->assign('PlayerPreferencesFormHREF', Page::create('preferences_player_processing.php')->href());
}
$template->assign('AccountPreferencesFormHREF', Page::create('preferences_account_processing.php')->href());

$template->assign('PreferencesConfirmFormHREF', Page::create('preferences_confirm.php')->href());

$template->assign('ChatSharingHREF', Page::create('chat_sharing.php')->href());

$transferAccounts = [];
$db = Smr\Database::getInstance();
$dbResult = $db->read('SELECT account_id,hof_name FROM account WHERE validated = ' . $db->escapeBoolean(true) . ' ORDER BY hof_name');
foreach ($dbResult->records() as $dbRecord) {
	$transferAccounts[$dbRecord->getInt('account_id')] = htmlspecialchars($dbRecord->getString('hof_name'));
}
$template->assign('TransferAccounts', $transferAccounts);
