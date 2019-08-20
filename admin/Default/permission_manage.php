<?php declare(strict_types=1);

$admin_id = SmrSession::getRequestVar('admin_id', false);

$template->assign('PageTopic', 'Manage Admin Permissions');

$container = create_container('skeleton.php', 'permission_manage.php');
$selectAdminHREF = SmrSession::getNewHREF($container);
$template->assign('SelectAdminHREF', $selectAdminHREF);

$db->query('SELECT account_id, login
			FROM account_has_permission JOIN account USING(account_id)
			GROUP BY account_id');
while ($db->nextRecord()) {
	$accountID = $db->getInt('account_id');
	$container['admin_id'] = $accountID;
	$adminLinks[$accountID] = [
		'href' => SmrSession::getNewHREF($container),
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

	$container = create_container('permission_manage_processing.php');
	$container['admin_id'] = $admin_id;
	$processingHREF = SmrSession::getNewHREF($container);
	$template->assign('ProcessingHREF', $processingHREF);

	$template->assign('PermissionCategories',
	                  AdminPermissions::getPermissionsByCategory());
}
