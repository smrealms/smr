<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Globals;
use Smr\Page\AccountPage;
use Smr\Page\ReusableTrait;
use Smr\Template;

class FormOpen extends AccountPage {

	use ReusableTrait;

	public function build(Account $account, Template $template): void {
		$template->pageTopic = 'Open/Close Forms';

		$isOpen = Globals::isFeatureRequestOpen();
		$template->pageRenderer = fn() => FormOpenRenderer::render(
			Color: $isOpen ? 'green' : 'red',
			Status: $isOpen ? 'OPEN' : 'CLOSED',
			ToggleHREF: new FormOpenProcessor(
				isOpen: $isOpen,
				type: 'FEATURE',
			)->href(),
			Action: $isOpen ? 'Close' : 'Open',
		);
	}

}
