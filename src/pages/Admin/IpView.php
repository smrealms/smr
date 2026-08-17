<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Page\AccountPage;
use Smr\Page\ReusableTrait;
use Smr\Template;

class IpView extends AccountPage {

	use ReusableTrait;
	public function build(Account $account, Template $template): void {
		$template->pageTopic = 'IP Search';

		$template->pageRenderer = fn() => IpViewRenderer::render(
			IpFormHref: new IpViewResults()->href(),
		);
	}

}
