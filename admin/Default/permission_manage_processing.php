<?php

if ($_REQUEST['action'] == 'Change') {
	// delete everything first
	$db->query('DELETE
				FROM account_has_permission
				WHERE account_id = ' . $db->escapeNumber($var['admin_id']));

	if (is_array($_POST['permission_ids'])) {
		foreach ($_POST['permission_ids'] as $permission_id) {
			$db->query('REPLACE
						INTO account_has_permission
						(account_id, permission_id)
						VALUES (' . $db->escapeNumber($var['admin_id']) . ', '.$db->escapeNumber($permission_id).')');
		}
	}
}

forward(create_container('skeleton.php', 'permission_manage.php'));

?>