<?php

$template->assign('PageTopic','Send Message');

require_once(get_file_loc('menu.inc'));
create_message_menu();

$container = create_container('message_send_processing.php');
transfer('receiver');
$template->assign('MessageSendFormHref',SmrSession::get_new_href($container));

if (!empty($var['receiver']))
	$template->assignByRef('Reciever', SmrPlayer::getPlayer($var['receiver'], $player->getGameID()));
else
	$template->assign('Reciever', 'All Online');
if(isset($var['preview']))
	$template->assign('Preview', $var['preview']);
?>