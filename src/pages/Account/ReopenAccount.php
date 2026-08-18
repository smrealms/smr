<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Smr\Account;
use Smr\Page\AccountPage;
use Smr\Template;

class ReopenAccount extends AccountPage {

	public function build(Account $account, Template $template): void {
		// This page should only be accessed by players whose accounts
		// have been closed at their own request.
		$disabled = $account->isDisabled();

		if ($disabled === false) {
			create_error('Your account is not disabled!');
		}
		if ($disabled['Reason'] !== CLOSE_ACCOUNT_BY_REQUEST_REASON) {
			create_error('You are not allowed to re-open your account!');
		}

		$template->pageTopic = 'Re-Open Account?';

		$template->pageRenderer = fn() => ReopenAccountRenderer::render(
			new ReopenAccountProcessor()->href(),
		);
	}

}
