<?php

if(isset($var['errorMsg'])) {
	$template->assign('ErrorMessage',$var['errorMsg']);
}
if (isset($var['msg'])) {
	$template->assign('Message',$var['msg']);
}

$db->query('SELECT * FROM account_has_permission JOIN permission USING (permission_id) WHERE account_id = '.$db->escapeNumber($account->getAccountID()));

if ($db->getNumRows()) {
	$adminPermissions = array();
	while ($db->nextRecord()) {
		$adminPermissions[] = array( 'PermissionLink' => $db->getField('link_to')?SmrSession::getNewHREF(create_container('skeleton.php',$db->getField('link_to'))):false,
					'Name' => $db->getField('permission_name'));
	}
	$template->assign('AdminPermissions',$adminPermissions);
}
