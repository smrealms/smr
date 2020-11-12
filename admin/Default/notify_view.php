<?php declare(strict_types=1);
$template->assign('PageTopic', 'Viewing Reported Messages');

require_once(get_file_loc('message.functions.inc'));

$container = create_container('notify_delete_processing.php');
$template->assign('DeleteHREF', SmrSession::getNewHREF($container));

$db->query('SELECT * FROM message_notify');
$messages = [];
while ($db->nextRecord()) {
	$gameID = $db->getInt('game_id');
	$sender = getMessagePlayer($db->getInt('from_player_id'), $gameID);
	$receiver = getMessagePlayer($db->getInt('to_player_id'), $gameID);

	$container = create_container('skeleton.php', 'notify_reply.php');
	$container['offender_player_id'] = $db->getInt('from_player_id');
	$container['offended_player_'] = $db->getInt('to_player_id');
	$container['game_id'] = $gameID;

	$offender = $sender;
	if (is_object($sender)) {
		$sender_acc = $sender->getAccount();
		$offender = $sender_acc->getLogin() . ' (' . $sender_acc->getAccountID() . ')';
		$offender .= ' a.k.a ' . $sender->getDisplayName();
	}
	$senderLink = create_link($container, $offender);

	$offended = $receiver;
	if (is_object($receiver)) {
		$receiver_acc = $receiver->getAccount();
		$offended = $receiver_acc->getLogin() . ' (' . $receiver_acc->getAccountID() . ')';
		$offended .= ' a.k.a ' . $receiver->getDisplayName();
	}
	$receiverLink = create_link($container, $offended);

	if (!Globals::isValidGame($gameID)) {
		$gameName = 'Game no longer exists';
	} else {
		$gameName = SmrGame::getGame($gameID)->getDisplayName();
	}

	$messages[] = [
		'notifyID' => $db->getInt('notify_id'),
		'senderLink' => $senderLink,
		'receiverLink' => $receiverLink,
		'gameName' => $gameName,
		'sentDate' => date(DATE_FULL_SHORT, $db->getInt('sent_time')),
		'reportDate' => date(DATE_FULL_SHORT, $db->getInt('notify_time')),
		'text' => bbifyMessage($db->getField('text')),
	];
}
$template->assign('Messages', $messages);
