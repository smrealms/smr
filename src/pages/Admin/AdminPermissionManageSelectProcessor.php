<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Page\AccountPageProcessor;
use Smr\Request;
use SmrAccount;

class AdminPermissionManageSelectProcessor extends AccountPageProcessor {

	public function build(SmrAccount $account): never {
		$adminAccountID = Request::getInt('admin_id');
		(new AdminPermissionManage($adminAccountID))->go();
	}

}
