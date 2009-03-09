<?

$template->assign('PageTopic','REPLY TO REPORTED MESSAGES');

$container = array();
$container['url']        = 'box_reply_processing.php';
transfer('game_id');
transfer('sender_id');
$PHP_OUTPUT.=create_echo_form($container);
$sender =& SmrPlayer::getPlayer($var['sender_id'], $var['game_id']);
$senderAcc =& SmrAccount::getAccount($var['sender_id']);
$PHP_OUTPUT.=('To : '.$sender->getPlayerName().' a.k.a '.$senderAcc->login);
$PHP_OUTPUT.=('<br /><input type="text" value="0" name="BanPoints" size="4" /> Points<br />');
$PHP_OUTPUT.=('<textarea name="message" id="InputFields" style="width:350px;height:100px;"></textarea><br /><br />');

$PHP_OUTPUT.=create_submit('Send messages');
$PHP_OUTPUT.=('</form>');

?>