<?php declare(strict_types=1);

if (Request::get('action') == 'Change') {
	// Check to see if admin previously was displaying Admin tag
	$hadAdminTag = SmrAccount::getAccount($var['admin_id'])->hasPermission(PERMISSION_DISPLAY_ADMIN_TAG);

	// delete everything first
	$db->query('DELETE
				FROM account_has_permission
				WHERE account_id = ' . $db->escapeNumber($var['admin_id']));

	// Grant permissions
	$permissions = Request::getIntArray('permission_ids', []);
	foreach ($permissions as $permission_id) {
		$db->query('REPLACE
						INTO account_has_permission
						(account_id, permission_id)
						VALUES (' . $db->escapeNumber($var['admin_id']) . ', ' . $db->escapeNumber($permission_id) . ')');
	}

	// Process adding/removing the Admin tag
	if (in_array(PERMISSION_DISPLAY_ADMIN_TAG, $permissions)) {
		// This might overwrite an existing unrelated tag.
		$tag = '<span class="blue">Admin</span>';
		$db->query('REPLACE INTO cpl_tag (account_id, tag, custom) VALUES (' . $db->escapeNumber($var['admin_id']) . ',' . $db->escapeString($tag) . ',0)');
	} elseif ($hadAdminTag) {
		// Only delete the tag if they previously had an admin tag;
		// otherwise we might accidentally delete an unrelated tag.
		$db->query('DELETE FROM cpl_tag WHERE custom=0 AND account_id=' . $db->escapeNumber($var['admin_id']));
	}
}

forward(create_container('skeleton.php', 'permission_manage.php'));
