<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\AdminPermissions;
use Smr\Database;
use Smr\Page\AccountPage;
use Smr\Page\ReusableTrait;
use Smr\Template;
use SmrAccount;

class AdminPermissionManage extends AccountPage {

	use ReusableTrait;

	public string $file = 'admin/permission_manage.php';

	public function __construct(
		private readonly ?int $adminAccountID = null
	) {}

	public function build(SmrAccount $account, Template $template): void {
		$admin_id = $this->adminAccountID;

		$template->assign('PageTopic', 'Manage Admin Permissions');

		$adminLinks = [];
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT account_id, login
					FROM account_has_permission JOIN account USING(account_id)
					GROUP BY account_id');
		foreach ($dbResult->records() as $dbRecord) {
			$accountID = $dbRecord->getInt('account_id');
			$container = new self($accountID);
			$adminLinks[$accountID] = [
				'href' => $container->href(),
				'name' => $dbRecord->getString('login'),
			];
		}
		$template->assign('AdminLinks', $adminLinks);

		if (empty($admin_id)) {
			// If we don't have an account_id here display an account list
			$validatedAccounts = [];
			$dbResult = $db->read('SELECT account_id, login
						FROM account
						WHERE validated = ' . $db->escapeBoolean(true) . '
						ORDER BY login');
			foreach ($dbResult->records() as $dbRecord) {
				$accountID = $dbRecord->getInt('account_id');
				if (!array_key_exists($accountID, $adminLinks)) {
					$validatedAccounts[$accountID] = $dbRecord->getString('login');
				}
			}
			$template->assign('ValidatedAccounts', $validatedAccounts);

			$template->assign('SelectAdminHREF', (new AdminPermissionManageSelectProcessor())->href());
		} else {
			// get the account that we're editing
			$editAccount = SmrAccount::getAccount($admin_id);
			$template->assign('EditAccount', $editAccount);

			$container = new AdminPermissionManageProcessor($admin_id);
			$processingHREF = $container->href();
			$template->assign('ProcessingHREF', $processingHREF);

			$template->assign('PermissionCategories', AdminPermissions::getPermissionsByCategory());
		}
	}

}
