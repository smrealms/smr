<?php

// mailer
require(LIB . 'phpMailer/class.phpmailer.php');

$mail = new PHPMailer();
$mail->From		     = 'newsletter@smrealms.de';
$mail->FromName		 = 'SMR Team';
$mail->Mailer		   = 'smtp';
$mail->SMTPKeepAlive    = true;

//$mail->ConfirmReadingTo       = 'newsletter-read@smrealms.de';

$mail->AddReplyTo('newsletter@smrealms.de', 'SMR Support');
$mail->Encoding = 'base64';
$mail->WordWrap = 72;

$db = new SmrMySqlDatabase();
$db->query('SELECT newsletter_id, newsletter_html, newsletter_text FROM newsletter ORDER BY newsletter_id DESC LIMIT 1');
if ($db->nextRecord())
{
	$mail->Subject = 'Space Merchant Realms Newsletter #' . $db->getField('newsletter_id');
	
	if(!empty($db->getField('newsletter_html')))
	{
		$mail->MsgHTML    = $db->getField('newsletter_html');
		$mail->AltBody = $db->getField('newsletter_text');
	}
	else
	{
		$mail->Body = $db->getField('newsletter_text');
	}
	// attach footer
//	$mail->Body   .= EOL.EOL.'Thank you,'.EOL.'   SMR Support Team'.EOL.EOL.'Note: You receive this e-mail because you are registered with Space Merchant Realms. If you prefer not to get any further notices please respond and we will disable your account.';
}

if($_REQUEST['to_email']=='*')
{
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
	
		// set a bounce address we can process later
		$mail->From = 'bounce_' . $account_id . '@smrealms.de';
		$mail->AddAddress($to_email, $to_name);
	
		if(!$mail->Send())
		{
			echo 'error.'.EOL . $mail->ErrorInfo;
			$mail->SmtpClose();
			ob_flush();
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
	$mail->AddAddress($_REQUEST['to_email'], $_REQUEST['to_email']);
	
	$mail->Send();
	$mail->SmtpClose();
}

forward(create_container('skeleton.php', 'newsletter_send.php'))

?>