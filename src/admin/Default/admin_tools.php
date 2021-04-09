<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$account = $session->getAccount();

if (isset($var['errorMsg'])) {
	$template->assign('ErrorMessage', $var['errorMsg']);
}
if (isset($var['msg'])) {
	$template->assign('Message', $var['msg']);
}

$adminPermissions = [];
foreach (array_keys($account->getPermissions()) as $permissionID) {
	list($name, $link, $categoryID) = AdminPermissions::getPermissionInfo($permissionID);
	$adminPermissions[$categoryID][] = [
		'Link' => empty($link) ? false : Page::create('skeleton.php', $link)->href(),
		'Name' => $name,
	];
}

$template->assign('AdminPermissions', $adminPermissions);
