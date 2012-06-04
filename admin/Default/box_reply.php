<?php

$template->assign('PageTopic','Reply To Reported Messages');

$container = create_container('box_reply_processing.php');
transfer('game_id');
transfer('sender_id');
$template->assign('BoxReplyFormHref',SmrSession::get_new_href($container));
$template->assignByRef('Sender',SmrPlayer::getPlayer($var['sender_id'], $var['game_id']));
$template->assignByRef('SenderAccount',SmrAccount::getAccount($var['sender_id']));
if(isset($var['Preview']))
	$template->assign('Preview', $var['Preview']);
if(isset($var['BanPoints']))
	$template->assign('BanPoints', $var['BanPoints']);
?>