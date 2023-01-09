<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Globals;
use Smr\Page\AccountPage;
use Smr\Page\ReusableTrait;
use Smr\Template;

class FormOpen extends AccountPage {

	use ReusableTrait;

	public string $file = 'admin/form_open.php';

	public function build(Account $account, Template $template): void {
		$template->assign('PageTopic', 'Open/Close Forms');

		$container = new FormOpenProcessor(
			isOpen: Globals::isFeatureRequestOpen(),
			type: 'FEATURE'
		);
		$template->assign('ToggleHREF', $container->href());

		$template->assign('Color', Globals::isFeatureRequestOpen() ? 'green' : 'red');
		$template->assign('Status', Globals::isFeatureRequestOpen() ? 'OPEN' : 'CLOSED');
		$template->assign('Action', Globals::isFeatureRequestOpen() ? 'Close' : 'Open');
	}

}
