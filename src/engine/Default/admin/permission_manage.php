<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();

$admin_id = $session->getRequestVarInt('admin_id', 0);

$template->assign('PageTopic', 'Manage Admin Permissions');

$container = Page::create('admin/permission_manage.php');
$selectAdminHREF = $container->href();
$template->assign('SelectAdminHREF', $selectAdminHREF);

$adminLinks = [];
$db = Smr\Database::getInstance();
$dbResult = $db->read('SELECT account_id, login
			FROM account_has_permission JOIN account USING(account_id)
			GROUP BY account_id');
foreach ($dbResult->records() as $dbRecord) {
	$accountID = $dbRecord->getInt('account_id');
	$container['admin_id'] = $accountID;
	$adminLinks[$accountID] = [
		'href' => $container->href(),
		'name' => $dbRecord->getField('login'),
	];
}
$template->assign('AdminLinks', $adminLinks);

if (empty($admin_id)) {
	// If we don't have an account_id here display an account list
	$validatedAccounts = [];
	$dbResult = $db->read('SELECT account_id, login
				FROM account
				WHERE validated = ' . $db->escapeBoolean(true) . '
				ORDER BY login');
	foreach ($dbResult->records() as $dbRecord) {
		$accountID = $dbRecord->getInt('account_id');
		if (!array_key_exists($accountID, $adminLinks)) {
			$validatedAccounts[$accountID] = $dbRecord->getField('login');
		}
	}
	$template->assign('ValidatedAccounts', $validatedAccounts);
} else {
	// get the account that we're editing
	$editAccount = SmrAccount::getAccount($admin_id);
	$template->assign('EditAccount', $editAccount);

	$container = Page::create('admin/permission_manage_processing.php');
	$container['admin_id'] = $admin_id;
	$processingHREF = $container->href();
	$template->assign('ProcessingHREF', $processingHREF);

	$template->assign('PermissionCategories', Smr\AdminPermissions::getPermissionsByCategory());
}
