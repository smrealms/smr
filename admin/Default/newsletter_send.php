<?php declare(strict_types=1);
$template->assign('PageTopic', 'Newsletter');

$template->assign('CurrentEmail', $account->getEmail());

$processingContainer = create_container('newsletter_send_processing.php');

// Get the most recent newsletter text for preview
$db = new SmrMySqlDatabase();
$db->query('SELECT newsletter_id, newsletter_html, newsletter_text FROM newsletter ORDER BY newsletter_id DESC LIMIT 1');
if ($db->nextRecord()) {
	$id = $db->getInt('newsletter_id');
	$template->assign('NewsletterId', $id);
	$template->assign('DefaultSubject', 'Space Merchant Realms Newsletter #' . $id);

	// Give both the template and processing container access to the message
	$processingContainer['newsletter_html'] = $db->getField('newsletter_html');
	$processingContainer['newsletter_text'] = $db->getField('newsletter_text');
	$template->assign('NewsletterHtml', $db->getField('newsletter_html'));
	$template->assign('NewsletterText', $db->getField('newsletter_text'));
}

// Create the form for the populated processing container
$template->assign('ProcessingForm', create_echo_form($processingContainer));
