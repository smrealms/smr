<?

// mailer
require(LIB . 'phpMailer/class.phpmailer.php');

$mail = new PHPMailer();

$mail->From		     = 'newsletter@smrealms.de';
$mail->FromName		 = 'SMR Team';
$mail->Mailer		   = 'smtp';
$mail->SMTPKeepAlive    = true;
//$mail->ConfirmReadingTo       = 'newsletter-read@smrealms.de';

$mail->AddReplyTo('support@smrealms.de', 'SMR Support');

$db->query('SELECT newsletter_id, newsletter FROM newsletter ORDER BY newsletter_id DESC LIMIT 1');
if ($db->nextRecord()) {

	$mail->Subject  = 'Space Merchant Realms Newsletter #' . $db->getField('newsletter_id');
	$mail->Body     = $db->getField('newsletter');

}

$mail->WordWrap = 72;
$mail->AddAddress($_REQUEST['to_email'], $_REQUEST['to_email']);
$mail->Send();
$mail->SmtpClose();

forward(create_container('skeleton.php', 'game_play.php'))

?>