<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\AdminPermissions;
use Smr\Page\AccountPage;
use Smr\Template;

class AdminTools extends AccountPage {

	public string $file = 'admin/admin_tools.php';

	public function __construct(
		private readonly ?string $message = null,
		private readonly ?string $errorMessage = null,
	) {}

	public function build(Account $account, Template $template): void {
		$template->assign('ErrorMessage', $this->errorMessage);
		$template->assign('Message', $this->message);

		$adminPermissions = [];
		foreach (array_keys($account->getPermissions()) as $permissionID) {
			[$name, $link, $categoryID] = AdminPermissions::getPermissionInfo($permissionID);
			$adminPermissions[$categoryID][] = [
				'Link' => $link === null ? false : (new $link())->href(),
				'Name' => $name,
			];
		}

		$template->assign('AdminPermissions', $adminPermissions);
	}

}
