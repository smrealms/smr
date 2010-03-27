<?php

// mailer
require(LIB . 'phpMailer/class.phpmailer.php');

$mail = new PHPMailer();

if($_REQUEST['to_email']=='*')
{
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
	if ($db->nextRecord())
	{
		$mail->Subject = 'Space Merchant Realms Newsletter #' . $db->getField('newsletter_id');
		$mail->Body    = $db->getField('newsletter');
	
		// attach footer
		$mail->Body   .= EOL.EOL.'Thank you,'.EOL.'   SMR Support Team'.EOL.EOL.'Note: You receive this e-mail because you are registered with Space Merchant Realms. If you prefer not to get any further notices please respond and we will disable your account.';
	}
	
	$mail->WordWrap = 72;
	
	// counter
	$i = 1;
	$total = 0;
	
	$db->query('SELECT account_id, email, first_name, last_name FROM newsletter_accounts WHERE account_id >= '.$i.' ORDER BY account_id');
	while ($db->nextRecord())
	{
		// get account data
		$account_id	= $db->getField('account_id');
		$to_email	= $db->getField('email');
		$to_name	= $db->getField('first_name') . ' ' . $db->getField('last_name');
	
		// debug output
		echo $account_id.'. Preparing mail for '.$to_name.' <'.$to_email.'>... ';
	
		// skip newsletter if account is closed.
		$db2->query('SELECT account_id FROM account_is_closed WHERE account_id = '.$account_id);
		if ($db2->getNumRows() > 0)
		{
			// debug output
			echo 'skipped.'.EOL;
	
			// go on with next account
			continue;
		}
	
		// set a bounce address we can process later
		$mail->From = 'bounce_' . $account_id . '@smrealms.de';
		$mail->AddAddress($to_email, $to_name);
	
		if(!$mail->Send())
		{
			echo 'error.'.EOL . $mail->ErrorInfo;
			$mail->SmtpClose();
			exit;
		}
		else
			echo 'sent.'.EOL;
	
		$total++;
	
		// Clear all addresses for next loop
		$mail->ClearAddresses();
	
		//sleep(1);
	
	}
	
	$mail->SmtpClose();
	
	echo 'Total '.$total.' mails sent.'.EOL;
	release_lock();
	exit();
}
else
{
	$mail->From		     = 'newsletter@smrealms.de';
	$mail->FromName		 = 'SMR Team';
	$mail->Mailer		   = 'smtp';
	$mail->SMTPKeepAlive    = true;
	//$mail->ConfirmReadingTo       = 'newsletter-read@smrealms.de';
	
	$mail->AddReplyTo('support@smrealms.de', 'SMR Support');
	
	$db= new SmrMySqlDatabase();
	$db->query('SELECT newsletter_id, newsletter FROM newsletter ORDER BY newsletter_id DESC LIMIT 1');
	if ($db->nextRecord())
	{
		$mail->Subject  = 'Space Merchant Realms Newsletter #' . $db->getField('newsletter_id');
		$mail->Body     = $db->getField('newsletter');
	}
	
	$mail->WordWrap = 72;
	$mail->AddAddress($_REQUEST['to_email'], $_REQUEST['to_email']);
	$mail->Send();
	$mail->SmtpClose();
}

forward(create_container('skeleton.php', 'newsletter_send.php'))

?>