<?php declare(strict_types=1);

use Smr\AdminPermissions;

		$template = Smr\Template::getInstance();
		$session = Smr\Session::getInstance();
		$var = $session->getCurrentVar();
		$account = $session->getAccount();

		if (isset($var['errorMsg'])) {
			$template->assign('ErrorMessage', $var['errorMsg']);
		}
		if (isset($var['msg'])) {
			$template->assign('Message', $var['msg']);
		}

		$adminPermissions = [];
		foreach (array_keys($account->getPermissions()) as $permissionID) {
			[$name, $link, $categoryID] = AdminPermissions::getPermissionInfo($permissionID);
			$adminPermissions[$categoryID][] = [
				'Link' => empty($link) ? false : Page::create($link)->href(),
				'Name' => $name,
			];
		}

		$template->assign('AdminPermissions', $adminPermissions);
