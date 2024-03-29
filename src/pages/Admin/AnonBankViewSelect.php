<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Page\AccountPage;
use Smr\Template;

class AnonBankViewSelect extends AccountPage {

	public string $file = 'admin/anon_acc_view_select.php';

	public function __construct(
		private readonly ?string $message = null,
	) {}

	public function build(Account $account, Template $template): void {
		//view anon acct activity.
		$template->assign('PageTopic', 'View Anonymous Account Info');

		$container = new AnonBankView();
		$template->assign('AnonViewHREF', $container->href());

		$template->assign('Message', $this->message);
	}

}
