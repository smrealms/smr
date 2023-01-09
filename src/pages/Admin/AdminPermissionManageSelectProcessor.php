<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Page\AccountPageProcessor;
use Smr\Request;

class AdminPermissionManageSelectProcessor extends AccountPageProcessor {

	public function build(Account $account): never {
		$adminAccountID = Request::getInt('admin_id');
		(new AdminPermissionManage($adminAccountID))->go();
	}

}
