#!/usr/bin/php -q
<?php

// config file
include( realpath(dirname(__FILE__)) . '/../htdocs/config.inc');

include(LIB . 'Default/SmrMySqlDatabase.class.inc');

// mailer
require(LIB . 'phpMailer/class.phpmailer.php');

// database objects
$db = new SmrMySqlDatabase();
$db2 = new SmrMySqlDatabase();

$mail = new PHPMailer();

$mail->FromName      = 'SMR Team';
$mail->Mailer        = 'smtp';
$mail->SMTPKeepAlive = true;
//$mail->ConfirmReadingTo	= 'newsletter-read@smrealms.de';

$mail->AddReplyTo('newsletter@smrealms.de', 'SMR Support');

$db->query('SELECT newsletter_id, newsletter FROM newsletter ORDER BY newsletter_id DESC LIMIT 1');
if ($db->nextRecord()) {

	$mail->Subject = 'Space Merchant Realms Newsletter #' . $db->getField('newsletter_id');
	$mail->Body    = $db->getField('newsletter');

	// attach footer
	$mail->Body   .= EOL.EOL.'Thank you,'.EOL.'   SMR Support Team'.EOL.EOL.'Note: You receive this e-mail because you are registered with Space Merchant Realms. If you prefer not to get any further notices please respond and we will disable your account.';

}

$mail->WordWrap = 72;

// counter
$i = 1;
$total = 0;

$db->query('SELECT account_id, login, email, first_name, last_name FROM account WHERE account_id >= '.$i.' AND validated = \'TRUE\' ORDER BY account_id');
while ($db->nextRecord()) {
//$db->query('SELECT account_id, login, email, first_name, last_name FROM account WHERE account_id >= '.$i.' AND validated = 'TRUE' AND account_id = 2');
//if ($db->nextRecord()) {

	// get account data
	$account_id	= $db->getField('account_id');
	$to_email	= $db->getField('email');
	$to_name	= $db->getField('first_name') . ' ' . $db->getField('last_name');

	// debug output
	$PHP_OUTPUT.=($account_id.'. Preparing mail for '.$to_name.' <'.$to_email.'>... ');

	// skip newsletter if account is closed.
	$db2->query('SELECT account_id FROM account_is_closed WHERE account_id = '.$account_id);
	if ($db2->getNumRows() > 0) {

		// debug output
		$PHP_OUTPUT.=('skipped.'.EOL);

		// go on with next account
		continue;

	}

	// set a bounce address we can process later
	$mail->From = 'bounce_' . $account_id . '@smrealms.de';
	$mail->AddAddress($to_email, $to_name);

	if(!$mail->Send()) {

		$PHP_OUTPUT.=('error.'.EOL . $mail->ErrorInfo);
		$mail->SmtpClose();
		exit;

	} else
		$PHP_OUTPUT.=('sent.'.EOL);

	$total++;

	// Clear all addresses for next loop
	$mail->ClearAddresses();

	//sleep(1);

}

$mail->SmtpClose();

$PHP_OUTPUT.=('Total '.$total.' mails sent.'.EOL);

?>
