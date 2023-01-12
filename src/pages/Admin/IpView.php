<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Page\AccountPage;
use Smr\Page\ReusableTrait;
use Smr\Template;

class IpView extends AccountPage {

	use ReusableTrait;

	public string $file = 'admin/ip_view.php';

	public function build(Account $account, Template $template): void {
		$template->assign('PageTopic', 'IP Search');

		$template->assign('IpFormHref', (new IpViewResults())->href());
	}

}
