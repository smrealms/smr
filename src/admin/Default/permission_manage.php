<?php declare(strict_types=1);

$admin_id = SmrSession::getRequestVarInt('admin_id', 0);

$template->assign('PageTopic', 'Manage Admin Permissions');

$container = Page::create('skeleton.php', 'permission_manage.php');
$selectAdminHREF = $container->href();
$template->assign('SelectAdminHREF', $selectAdminHREF);

$db->query('SELECT account_id, login
			FROM account_has_permission JOIN account USING(account_id)
			GROUP BY account_id');
while ($db->nextRecord()) {
	$accountID = $db->getInt('account_id');
	$container['admin_id'] = $accountID;
	$adminLinks[$accountID] = [
		'href' => $container->href(),
		'name' => $db->getField('login'),
	];
}
$template->assign('AdminLinks', $adminLinks);

if (empty($admin_id)) {
	// If we don't have an account_id here display an account list
	$validatedAccounts = [];
	$db->query('SELECT account_id, login
				FROM account
				WHERE validated = '.$db->escapeBoolean(true) . '
				ORDER BY login');
	while ($db->nextRecord()) {
		$accountID = $db->getInt('account_id');
		if (!array_key_exists($accountID, $adminLinks)) {
			$validatedAccounts[$accountID] = $db->getField('login');
		}
	}
	$template->assign('ValidatedAccounts', $validatedAccounts);
} else {
	// get the account that we're editing
	$editAccount = SmrAccount::getAccount($admin_id);
	$template->assign('EditAccount', $editAccount);

	$container = Page::create('permission_manage_processing.php');
	$container['admin_id'] = $admin_id;
	$processingHREF = $container->href();
	$template->assign('ProcessingHREF', $processingHREF);

	$template->assign('PermissionCategories',
	                  AdminPermissions::getPermissionsByCategory());
}
