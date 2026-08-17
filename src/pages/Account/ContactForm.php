<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Smr\Account;
use Smr\Page\AccountPage;
use Smr\Page\ReusableTrait;
use Smr\Template;

class ContactForm extends AccountPage {

	use ReusableTrait;

	public function build(Account $account, Template $template): void {
		$template->pageTopic = 'Contact Form';

		$template->pageRenderer = fn() => ContactFormRenderer::render(
			ProcessingHREF: new ContactFormProcessor()->href(),
			From: $account->getLogin(),
		);
	}

}
