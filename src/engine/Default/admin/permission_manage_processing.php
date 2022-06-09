<?php declare(strict_types=1);

if (Smr\Request::get('action') == 'Change') {
	$var = Smr\Session::getInstance()->getCurrentVar();

	// Check to see if admin previously was displaying Admin tag
	$hadAdminTag = SmrAccount::getAccount($var['admin_id'])->hasPermission(PERMISSION_DISPLAY_ADMIN_TAG);

	// delete everything first
	$db = Smr\Database::getInstance();
	$db->write('DELETE
				FROM account_has_permission
				WHERE account_id = ' . $db->escapeNumber($var['admin_id']));

	// Grant permissions
	$permissions = Smr\Request::getIntArray('permission_ids', []);
	foreach ($permissions as $permission_id) {
		$db->replace('account_has_permission', [
			'account_id' => $db->escapeNumber($var['admin_id']),
			'permission_id' => $db->escapeNumber($permission_id),
		]);
	}

	// Process adding/removing the Admin tag
	if (in_array(PERMISSION_DISPLAY_ADMIN_TAG, $permissions)) {
		// This might overwrite an existing unrelated tag.
		$tag = '<span class="blue">Admin</span>';
		$db->replace('cpl_tag', [
			'account_id' => $db->escapeNumber($var['admin_id']),
			'tag' => $db->escapeString($tag),
			'custom' => 0,
		]);
	} elseif ($hadAdminTag) {
		// Only delete the tag if they previously had an admin tag;
		// otherwise we might accidentally delete an unrelated tag.
		$db->write('DELETE FROM cpl_tag WHERE custom=0 AND account_id=' . $db->escapeNumber($var['admin_id']));
	}
}

Page::create('admin/permission_manage.php')->go();
