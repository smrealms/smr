<?php

if ($_POST['action'] == 'Change')
{
	// delete everything first
	$db->query('DELETE
				FROM account_has_permission
				WHERE account_id = ' . $var['admin_id']);

	if (is_array($_POST['permission_ids'])) {

		foreach ($_POST['permission_ids'] as $permission_id)
			$db->query('REPLACE
						INTO account_has_permission
						(account_id, permission_id)
						VALUES (' . $var['admin_id'] . ', '.$permission_id.')');

	}

}
else if ($_POST['action'] == 'Select Another User')
{
	unset($_POST['admin_id']);
}

forward(create_container('skeleton.php', 'permission_manage.php'));

?>