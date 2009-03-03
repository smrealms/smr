<?

$template->assign('PageTopic','Report a Message');

if (empty($var['message_id']))
	create_error('Please click the small yellow icon to report a message!');

// get message form db
$db->query('SELECT message_text
			FROM message
			WHERE message_id = ' . $var['message_id']);
if (!$db->nextRecord())
	create_error('Could not find the message you selected!');

$PHP_OUTPUT.=('You have selected the following message:<br /><br />');
$PHP_OUTPUT.=('<textarea id="InputFields" style="width:400px;height:300px;">' . stripslashes($db->getField('message_text')) . '</textarea>');

$PHP_OUTPUT.=('<p>Are you sure you want to notify this message to the admins?<br />');
$PHP_OUTPUT.=('<small><b>Please note:</b> Abuse of this system could end in disablement<br />Therefore, please only notify if the message is inappropriate</small></p>');

$container = create_container('message_notify_processing.php', '');
transfer('message_id');
transfer('sent_time');
transfer('notified_time');

$PHP_OUTPUT.=create_echo_form($container);
$PHP_OUTPUT.=create_submit('Yes');
$PHP_OUTPUT.=('&nbsp;&nbsp;');
$PHP_OUTPUT.=create_submit('No');
$PHP_OUTPUT.=('</form>');

?>