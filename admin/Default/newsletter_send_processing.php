<?php

// mailer
require(LIB . 'External/phpMailer/class.phpmailer.php');

$mail = new PHPMailer();
$mail->From				= 'newsletter@smrealms.de';
$mail->FromName			= 'SMR Team';

//$mail->ConfirmReadingTo       = 'newsletter-read@smrealms.de';

$mail->Encoding = 'base64';
$mail->WordWrap = 72;

$mail->Subject = $_REQUEST['subject'];

function set_mail_body(&$mail, $newsletterHtml, $newsletterText, $salutation) {
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
	if(!empty($newsletterHtml)) {
		$mail->MsgHTML($newsletterHtml);
		if(!empty($newsletterText)) {
			$mail->AltBody = $newsletterText;
		}
	} else {
		$mail->Body = $newsletterText;
	}

	// attach footer
	//$mail->Body   .= EOL.EOL.'Thank you,'.EOL.'   SMR Support Team'.EOL.EOL.'Note: You receive this e-mail because you are registered with Space Merchant Realms. If you prefer not to get any further notices please respond and we will disable your account.';
}

// Set the body of the e-mail
set_mail_body($mail, $var['newsletter_html'], $var['newsletter_text'],
              $_REQUEST['salutation']);

if($_REQUEST['to_email']=='*') {
	// counter
	$total = 0;

    $db->query('SELECT account_id, email, first_name, last_name FROM account WHERE validated="TRUE" AND email NOT IN ("noone@smrealms.de","NPC@smrealms.de") AND NOT(EXISTS(SELECT account_id FROM account_is_closed WHERE account_is_closed.account_id=account.account_id))');
	while ($db->nextRecord()) {
		// get account data
		$account_id	= $db->getField('account_id');
		$to_email	= $db->getField('email');
		$to_name	= $db->getField('first_name') . ' ' . $db->getField('last_name');

		// Reset the message body with personalized salutation, if requested
		if ($_REQUEST['salutation']) {
			$salutation = $_REQUEST['salutation'] . ' ' . $to_name . ',';
			set_mail_body($mail, $var['newsletter_html'], $var['newsletter_text'], $salutation);
		}

		// debug output
		echo $account_id.'. Preparing mail for '.$to_name.' <'.$to_email.'>... ';

		// set a bounce address we can process later
		$mail->AddReplyTo('bounce_' . $account_id . '@smrealms.de', 'SMR Support');
		$mail->AddAddress($to_email, $to_name);

		if(!$mail->Send()) {
			echo 'error.'.EOL . $mail->ErrorInfo;
			ob_flush();
			exit;
		}
		else
			echo 'sent.'.EOL;

		$total++;

		// Clear all addresses for next loop
		$mail->ClearAddresses();

	}

	echo 'Total '.$total.' mails sent.'.EOL;
	release_lock();
	exit();
}
else {

	$mail->AddReplyTo('support@smrealms.de', 'SMR Support');
	$mail->AddAddress($_REQUEST['to_email'], $_REQUEST['to_email']);

	$mail->Send();
}

forward(create_container('skeleton.php', 'newsletter_send.php'))

?>
