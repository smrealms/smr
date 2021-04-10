<?php declare(strict_types=1);
$template->assign('PageTopic', 'Preferences');

if (isset($var['reason'])) {
	$template->assign('Reason', $var['reason']);
}

$template->assign('PreferencesFormHREF', Page::create('preferences_processing.php', '')->href());

$template->assign('PreferencesConfirmFormHREF', Page::create('skeleton.php', 'preferences_confirm.php')->href());

$template->assign('ChatSharingHREF', Page::create('skeleton.php', 'chat_sharing.php')->href());

$transferAccounts = array();
$db = Smr\Database::getInstance();
$db->query('SELECT account_id,hof_name FROM account WHERE validated = ' . $db->escapeBoolean(true) . ' ORDER BY hof_name');
while ($db->nextRecord()) {
	$transferAccounts[$db->getInt('account_id')] = htmlspecialchars($db->getField('hof_name'));
}
$template->assign('TransferAccounts', $transferAccounts);
