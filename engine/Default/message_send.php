<?

$smarty->assign('PageTopic','SEND MESSAGE');

include(ENGINE . 'global/menue.inc');
$PHP_OUTPUT.=create_message_menue();

$PHP_OUTPUT.=('<p>');

$container = array();
$container['url'] = 'message_send_processing.php';
transfer('receiver');

$PHP_OUTPUT.=create_echo_form($container);
$PHP_OUTPUT.=('<p><small><b>From:</b> '.$player->getPlayerName().' ('.$player->getPlayerID().')<br />');

if (!empty($var['receiver'])) {

	$receiver =& SmrPlayer::getPlayer($var['receiver'], SmrSession::$game_id);
	$PHP_OUTPUT.=('<b>To:</b> '.$receiver->getPlayerName().' ('.$receiver->getPlayerID().')</small></p>');

} else $PHP_OUTPUT.=('<b>To:</b> All Online</small></p>');

$PHP_OUTPUT.=('<textarea name="message" id="InputFields" style="width:350px;height:100px;"></textarea><br /><br />');
$PHP_OUTPUT.=create_submit('Send message');
$PHP_OUTPUT.=('</form>');
$PHP_OUTPUT.=('</p>');

?>