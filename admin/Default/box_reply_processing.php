<?php

$message = trim($_REQUEST['message']);
if($_REQUEST['action'] == 'Preview message') {
	$container = create_container('skeleton.php','box_reply.php');
	$container['BanPoints'] = $_REQUEST['BanPoints'];
	transfer('game_id');
	transfer('sender_id');
	$container['Preview'] = $message;
	forward($container);
}

if(!empty($message)) {
	SmrPlayer::sendMessageFromAdmin($var['game_id'], $var['sender_id'], $_REQUEST['message']);
	//do we have points?
	if ($_REQUEST['BanPoints']) {
		$reasonID = 7;
		$suspicion = 'Inappropriate Actions';
		$senderAccount =& SmrAccount::getAccount($var['sender_id']);
		$senderAccount->addPoints($_REQUEST['BanPoints'],$account,$reasonID,$suspicion);
	}
}
forward(create_container('skeleton.php', 'box_view.php'));

?>