<?php declare(strict_types=1);
$template->assign('PageTopic', 'Viewing Reported Messages');

require_once(get_file_loc('messages.inc.php'));

$container = create_container('notify_delete_processing.php');
$template->assign('DeleteHREF', SmrSession::getNewHREF($container));

$db->query('SELECT * FROM message_notify');
$messages = [];
while ($db->nextRecord()) {
	$gameID = $db->getInt('game_id');
	$sender = getMessagePlayer($db->getInt('from_id'), $gameID);
	$receiver = getMessagePlayer($db->getInt('to_id'), $gameID);

	$container = create_container('skeleton.php', 'notify_reply.php');
	$container['offender'] = $db->getInt('from_id');
	$container['offended'] = $db->getInt('to_id');
	$container['game_id'] = $gameID;

	/**
	 * @var $messagePlayer SmrPlayer | string
	 */
	$getName = function($messagePlayer) use ($container, $account) : string {
		$name = $messagePlayer;
		if ($messagePlayer instanceof SmrPlayer) {
			$name = $messagePlayer->getAccount()->getLogin();
			$name .= ' (' . $messagePlayer->getAccountID() . ')';
			$name .= ' a.k.a ' . $messagePlayer->getDisplayName();
		}
		// If we can send admin messages, make the names reply links
		if ($account->hasPermission(PERMISSION_SEND_ADMIN_MESSAGE)) {
			$name = create_link($container, $name);
		}
		return $name;
	};

	if (!Globals::isValidGame($gameID)) {
		$gameName = 'Game ' . $gameID . ' no longer exists';
	} else {
		$gameName = SmrGame::getGame($gameID)->getDisplayName();
	}

	$messages[] = [
		'notifyID' => $db->getInt('notify_id'),
		'senderName' => $getName($sender),
		'receiverName' => $getName($receiver),
		'gameName' => $gameName,
		'sentDate' => date(DATE_FULL_SHORT, $db->getInt('sent_time')),
		'reportDate' => date(DATE_FULL_SHORT, $db->getInt('notify_time')),
		'text' => bbifyMessage($db->getField('text')),
	];
}
$template->assign('Messages', $messages);
