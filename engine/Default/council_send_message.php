<?

$db->query('SELECT * FROM race WHERE race_id = ' . $var['race_id']);
if ($db->nextRecord())
	$race_name = $db->getField('race_name');

$template->assign('PageTopic','Send message to ruling council of the '.$race_name);

include(ENGINE . 'global/menue.inc');
$PHP_OUTPUT.=create_message_menue();

$PHP_OUTPUT.=('<p>');

$container = array();
$container['url'] = 'council_send_message_processing.php';
transfer('race_id');

$PHP_OUTPUT.=create_echo_form($container);
$PHP_OUTPUT.=('<p><small><b>From:</b> '.$player->getPlayerName().' ('.$player->getPlayerID().')<br />');

$PHP_OUTPUT.=('<b>To:</b> Ruling Council of '.$race_name.'</small></p>');

$PHP_OUTPUT.=('<textarea name="message" id="InputFields" style="width:350px;height:100px;"></textarea><br /><br />');
$PHP_OUTPUT.=create_submit('Send message');
$PHP_OUTPUT.=('</form>');
$PHP_OUTPUT.=('</p>');

?>