<?php

if(isset($var['errorMsg'])) {
	$template->assign('ErrorMessage',$var['errorMsg']);
}
if (isset($var['msg'])) {
	$template->assign('Message',$var['msg']);
}

$adminPermissions = [];
foreach (array_keys($account->getPermissions()) as $permissionID) {
	list($name, $link, $categoryID) = AdminPermissions::getPermissionInfo($permissionID);
	$adminPermissions[$categoryID][] = [
		'Link' => empty($link) ? false : SmrSession::getNewHREF(create_container('skeleton.php', $link)),
		'Name' => $name,
	];
}

$template->assign('AdminPermissions',$adminPermissions);
