<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Database;
use Smr\Page\AccountPage;
use Smr\Template;

class NewsletterSend extends AccountPage {

	public function build(Account $account, Template $template): void {
		$template->pageTopic = 'Newsletter';

		// Get the most recent newsletter text for preview
		$db = Database::getInstance();
		$dbResult = $db->select('newsletter', orderBy: ['newsletter_id'], order: ['DESC'], limit: 1);
		if ($dbResult->hasRecord()) {
			$dbRecord = $dbResult->record();
			$id = $dbRecord->getInt('newsletter_id');
			$defaultSubject = 'Space Merchant Realms Newsletter #' . $id;

			// Give both the template and processing container access to the message
			$processingContainer = new NewsletterSendProcessor(
				newsletterHtml: $dbRecord->getString('newsletter_html'),
				newsletterText: $dbRecord->getString('newsletter_text'),
			);

			// Create the form for the populated processing container
			$template->pageRenderer = fn() => NewsletterSendRenderer::render(
				CurrentEmail: $account->getEmail(),
				NewsletterId: $id,
				DefaultSubject: $defaultSubject,
				NewsletterHtml: $dbRecord->getString('newsletter_html'),
				NewsletterText: $dbRecord->getString('newsletter_text'),
				ProcessingHREF: $processingContainer->href(),
			);
		} else {
			$template->pageRenderer = fn() => NewsletterSendRenderer::renderEmpty();
		}
	}

}
