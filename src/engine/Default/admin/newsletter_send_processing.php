<?php declare(strict_types=1);

$var = Smr\Session::getInstance()->getCurrentVar();

// mailer
$mail = setupMailer();
$mail->setFrom('newsletter@smrealms.de', 'SMR Team');

$mail->Encoding = 'base64';

$mail->Subject = Smr\Request::get('subject');

function set_mail_body(PHPMailer\PHPMailer\PHPMailer $mail, ?string $newsletterHtml, ?string $newsletterText, ?string $salutation): void {
	// Prepend the salutation if one is given
	if ($salutation) {
		if (!empty($newsletterHtml)) {
			$newsletterHtml = $salutation . '<br /><br />' . $newsletterHtml;
		}
		if (!empty($newsletterText)) {
			$newsletterText = $salutation . EOL . EOL . $newsletterText;
		}
	}

	// Set the body text, giving preference to HTML
	if (!empty($newsletterHtml)) {
		$mail->msgHTML($newsletterHtml);
		if (!empty($newsletterText)) {
			$mail->AltBody = $newsletterText;
		}
	} else {
		$mail->Body = $newsletterText;
	}

	// attach footer
	//$mail->Body   .= EOL.EOL.'Thank you,'.EOL.'   SMR Support Team'.EOL.EOL.'Note: You receive this e-mail because you are registered with Space Merchant Realms. If you prefer not to get any further notices please respond and we will disable your account.';
}

// Set the body of the e-mail
set_mail_body(
	$mail,
	$var['newsletter_html'],
	$var['newsletter_text'],
	Smr\Request::get('salutation')
);

if (Smr\Request::get('to_email') == '*') {
	// Send the newsletter to all players.
	// Disable output buffering here so we can monitor the progress.
	header('X-Accel-Buffering: no'); // disable Nginx output buffering
	ob_implicit_flush(); // instruct PHP to flush after every output call
	ob_end_flush(); // turn off PHP output buffering

	// Skip all smrealms.de addresses (NPC, multi) to avoid spamming ourselves.
	$db = Smr\Database::getInstance();
	$dbResult = $db->read('SELECT account_id, email, login FROM account WHERE validated="TRUE" AND email NOT LIKE "%@smrealms.de" AND NOT(EXISTS(SELECT account_id FROM account_is_closed WHERE account_is_closed.account_id=account.account_id))');

	$total = $dbResult->getNumRecords();
	echo 'Will send ' . $total . ' mails...<br /><br />';

	// counter
	$sent = 0;

	// Depending on the total number of accounts, this may take a while.
	// Give PHP an unlimited time to send (ignored if PHP is compiled with
	// --enable-safe-mode). However, you may hit a browser or HTTP timeout.
	set_time_limit(0);

	foreach ($dbResult->records() as $dbRecord) {
		// get account data
		$account_id = $dbRecord->getInt('account_id');
		$to_email = $dbRecord->getString('email');
		$to_name = $dbRecord->getString('login');

		// Reset the message body with personalized salutation, if requested
		$salutation = Smr\Request::get('salutation');
		if (!empty($salutation)) {
			$salutation .= ' ' . $to_name . ',';
			set_mail_body($mail, $var['newsletter_html'], $var['newsletter_text'], $salutation);
		}

		// debug output
		echo '[' . $account_id . '] Preparing mail for ' . $to_name . ' (' . $to_email . ')... ';

		// set a bounce address we can process later
		$mail->addReplyTo('bounce_' . $account_id . '@smrealms.de', 'SMR Support');
		$mail->addAddress($to_email, $to_name);

		if (!$mail->send()) {
			echo 'error.' . EOL . $mail->ErrorInfo;
			exit;
		}

		$sent++;
		echo 'sent.<br />';
		if (($sent % 10) == 0) {
			echo 'Sent ' . $sent . ' of ' . $total . ' mails.<br /><br />';
		}

		// Clear all addresses for next loop
		$mail->clearReplyTos();
		$mail->clearAddresses();
	}

	echo '<br />Done! Total ' . $sent . ' mails sent.';
	exit();
} else {

	$mail->addReplyTo('support@smrealms.de', 'SMR Support');
	$mail->addAddress(Smr\Request::get('to_email'));

	if (!$mail->send()) {
		echo 'error.' . EOL . $mail->ErrorInfo;
		exit;
	}
}

Page::create('admin/newsletter_send.php')->go();
