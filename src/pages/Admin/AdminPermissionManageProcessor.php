<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Database;
use Smr\Page\AccountPageProcessor;
use Smr\Request;

class AdminPermissionManageProcessor extends AccountPageProcessor {

	public function __construct(
		private readonly int $adminAccountID,
	) {}

	public function build(Account $account): never {
		if (Request::get('action') === 'Change') {
			// Check to see if admin previously was displaying Admin tag
			$hadAdminTag = Account::getAccount($this->adminAccountID)->hasPermission(PERMISSION_DISPLAY_ADMIN_TAG);

			// delete everything first
			$db = Database::getInstance();
			$db->delete('account_has_permission', [
				'account_id' => $this->adminAccountID,
			]);

			// Grant permissions
			$permissions = Request::getIntArray('permission_ids', []);
			foreach ($permissions as $permission_id) {
				$db->replace('account_has_permission', [
					'account_id' => $this->adminAccountID,
					'permission_id' => $permission_id,
				]);
			}

			// Process adding/removing the Admin tag
			if (in_array(PERMISSION_DISPLAY_ADMIN_TAG, $permissions, true)) {
				// This might overwrite an existing unrelated tag and/or custom rank
				$tag = '<span class="blue">Admin</span>';
				$db->replace('cpl_tag', [
					'account_id' => $this->adminAccountID,
					'tag' => $tag,
					'custom' => 0,
					'custom_rank' => '',
				]);
			} elseif ($hadAdminTag) {
				// Only delete the tag if they previously had an admin tag;
				// otherwise we might accidentally delete an unrelated tag.
				$db->delete('cpl_tag', [
					'custom' => 0,
					'account_id' => $this->adminAccountID,
				]);
			}
		}

		(new AdminPermissionManage())->go();
	}

}
