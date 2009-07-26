<?

$template->assign('PageTopic','REPLY TO REPORTED MESSAGES');

$container = array();
$container['url']        = 'notify_reply_processing.php';
transfer('game_id');
transfer('offended');
transfer('offender');
$PHP_OUTPUT.=('Leave a message box blank to not reply to that player.');
$PHP_OUTPUT.=create_echo_form($container);
$offender =& SmrPlayer::getPlayer($var['offender'], $var['game_id']);
$offended =& SmrPlayer::getPlayer($var['offended'], $var['game_id']);
$offenderAcc =& SmrAccount::getAccount($var['offender']);
$offendedAcc =& SmrAccount::getAccount($var['offended']);
$PHP_OUTPUT.=('To : '.$offender->getPlayerName().' a.k.a '.$offenderAcc->login.' (Offender)');
$PHP_OUTPUT.=('<br /><input type="text" value="0" name="offenderBanPoints" size="4" /> Points<br />');
$PHP_OUTPUT.=('<textarea name="offenderReply" id="InputFields" cols="20" rows="30"></textarea><br /><br />');

$PHP_OUTPUT.=('To : '.$offended->getPlayerName().' a.k.a '.$offendedAcc->login.' (Offended)');
$PHP_OUTPUT.=('<br /><input type="text" value="0" name="offendedBanPoints" size="4" /> Points<br />');
$PHP_OUTPUT.=('<textarea name="offendedReply" id="InputFields" cols="20" rows="30"></textarea><br /><br />');

$PHP_OUTPUT.=create_submit('Send messages');
$PHP_OUTPUT.=('</form>');

?>