<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$account = $session->getAccount();

$template->assign('PageTopic', 'Newsletter');

$template->assign('CurrentEmail', $account->getEmail());

$processingContainer = Page::create('admin/newsletter_send_processing.php');

// Get the most recent newsletter text for preview
$db = Smr\Database::getInstance();
$dbResult = $db->read('SELECT newsletter_id, newsletter_html, newsletter_text FROM newsletter ORDER BY newsletter_id DESC LIMIT 1');
if ($dbResult->hasRecord()) {
	$dbRecord = $dbResult->record();
	$id = $dbRecord->getInt('newsletter_id');
	$template->assign('NewsletterId', $id);
	$template->assign('DefaultSubject', 'Space Merchant Realms Newsletter #' . $id);

	// Give both the template and processing container access to the message
	$processingContainer['newsletter_html'] = $dbRecord->getString('newsletter_html');
	$processingContainer['newsletter_text'] = $dbRecord->getString('newsletter_text');
	$template->assign('NewsletterHtml', $dbRecord->getString('newsletter_html'));
	$template->assign('NewsletterText', $dbRecord->getString('newsletter_text'));
}

// Create the form for the populated processing container
$template->assign('ProcessingHREF', $processingContainer->href());
