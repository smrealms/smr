<?php

$template->assign('PageTopic','Send Message');

include(get_file_loc('menue.inc'));
create_message_menue();

$container = create_container('message_send_processing.php');
transfer('receiver');
$template->assign('MessageSendFormHref',SmrSession::get_new_href($container));

if (!empty($var['receiver']))
	$template->assignByRef('Reciever', SmrPlayer::getPlayer($var['receiver'], SmrSession::$game_id));
else
	$template->assign('Reciever', 'All Online');
if(isset($var['preview']))
	$template->assign('Preview', $var['preview']);
?>