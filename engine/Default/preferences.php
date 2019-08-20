<?php declare(strict_types=1);
$template->assign('PageTopic', 'Preferences');

if (isset($var['reason'])) {
	$template->assign('Reason', $var['reason']);
}

$template->assign('PreferencesFormHREF', SmrSession::getNewHREF(create_container('preferences_processing.php', '')));

$template->assign('PreferencesConfirmFormHREF', SmrSession::getNewHREF(create_container('skeleton.php', 'preferences_confirm.php')));

$template->assign('ChatSharingHREF', SmrSession::getNewHREF(create_container('skeleton.php', 'chat_sharing.php')));

$transferAccounts = array();
$db->query('SELECT account_id,hof_name FROM account WHERE validated = ' . $db->escapeBoolean(true) . ' ORDER BY hof_name');
while ($db->nextRecord()) {
	$transferAccounts[$db->getInt('account_id')] = htmlspecialchars($db->getField('hof_name'));
}
$template->assign('TransferAccounts', $transferAccounts);
