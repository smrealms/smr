<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Smr\Account;
use Smr\Page\AccountPage;
use Smr\Template;

class Validate extends AccountPage {

	public function __construct(
		private readonly ?string $message = null,
	) {}

	public function build(Account $account, Template $template): void {
		$template->pageTopic = 'Validation Reminder';

		$template->pageRenderer = fn() => ValidateRenderer::render(
			Message: $this->message,
			ValidatePage: new ValidateProcessor(),
			ThisAccount: $account,
			PreferencesLink: new Preferences()->href(),
		);
	}

}
