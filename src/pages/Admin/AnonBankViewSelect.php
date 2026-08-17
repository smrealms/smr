<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Page\AccountPage;
use Smr\Template;

class AnonBankViewSelect extends AccountPage {

	public function __construct(
		private readonly ?string $message = null,
	) {}

	public function build(Account $account, Template $template): void {
		//view anon acct activity.
		$template->pageTopic = 'View Anonymous Account Info';

		$template->pageRenderer = fn() => AnonBankViewSelectRenderer::render(
			Message: $this->message,
			AnonViewHREF: new AnonBankView()->href(),
		);
	}

}
