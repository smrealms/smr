<?php
$template->assign('PageTopic', 'Viewing Reported Messages');

require_once(get_file_loc('message.functions.inc'));

$container = create_container('notify_delete_processing.php');
$template->assign('DeleteHREF', SmrSession::getNewHREF($container));

$db->query('SELECT * FROM message_notify');
$messages = [];
while ($db->nextRecord()) {
	$gameID = $db->getField('game_id');
	$sender = getMessagePlayer($db->getInt('from_id'), $gameID);
	$receiver = getMessagePlayer($db->getInt('to_id'), $gameID);

	$container = create_container('skeleton.php', 'notify_reply.php');
	$container['offender'] = $db->getInt('from_id');
	$container['offended'] = $db->getInt('to_id');
	$container['game_id'] = $gameID;

	$offender = $sender;
	if (is_object($sender)) {
		$sender_acc = $sender->getAccount();
		$offender = $sender_acc->getLogin() . ' (' . $sender_acc->getAccountID() . ')';
		if ($sender_acc->getLogin() != $sender->getPlayerName()) {
			$offender .= ' a.k.a ' . $sender->getPlayerName();
		}
	}
	$senderLink = create_link($container, $offender);

	$offended = $receiver;
	if (is_object($receiver)) {
		$receiver_acc = $receiver->getAccount();
		$offended = $receiver_acc->getLogin() . ' (' . $receiver_acc->getAccountID() . ')';
		if ($receiver_acc->getLogin() != $receiver->getPlayerName()) {
			$offended .= ' a.k.a ' . $receiver->getPlayerName();
		}
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
		'sentDate' => date(DATE_FULL_SHORT, $db->getField('sent_time')),
		'reportDate' => date(DATE_FULL_SHORT, $db->getField('notify_time')),
		'text' => bbifyMessage($db->getField('text')),
	];
}
$template->assign('Messages', $messages);
