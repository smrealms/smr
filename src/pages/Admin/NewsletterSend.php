<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Database;
use Smr\Page\AccountPage;
use Smr\Template;
use SmrAccount;

class NewsletterSend extends AccountPage {

	public string $file = 'admin/newsletter_send.php';

	public function build(SmrAccount $account, Template $template): void {
		$template->assign('PageTopic', 'Newsletter');

		$template->assign('CurrentEmail', $account->getEmail());

		// Get the most recent newsletter text for preview
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT newsletter_id, newsletter_html, newsletter_text FROM newsletter ORDER BY newsletter_id DESC LIMIT 1');
		if ($dbResult->hasRecord()) {
			$dbRecord = $dbResult->record();
			$id = $dbRecord->getInt('newsletter_id');
			$template->assign('NewsletterId', $id);
			$template->assign('DefaultSubject', 'Space Merchant Realms Newsletter #' . $id);

			// Give both the template and processing container access to the message
			$processingContainer = new NewsletterSendProcessor(
				newsletterHtml: $dbRecord->getString('newsletter_html'),
				newsletterText: $dbRecord->getString('newsletter_text'),
			);
			$template->assign('NewsletterHtml', $dbRecord->getString('newsletter_html'));
			$template->assign('NewsletterText', $dbRecord->getString('newsletter_text'));

			// Create the form for the populated processing container
			$template->assign('ProcessingHREF', $processingContainer->href());
		}
	}

}
