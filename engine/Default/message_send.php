<?php

$template->assign('PageTopic', 'Send Message');

Menu::messages();

$container = create_container('message_send_processing.php');
transfer('receiver');
$template->assign('MessageSendFormHref', SmrSession::getNewHREF($container));

if (!empty($var['receiver']))
	$template->assign('Receiver', SmrPlayer::getPlayer($var['receiver'], $player->getGameID()));
else
	$template->assign('Receiver', 'All Online');
if (isset($var['preview']))
	$template->assign('Preview', $var['preview']);
