<?php declare(strict_types=1);

require_once(get_file_loc('message.functions.inc'));

if (!isset($var['box_type_id'])) {
	$template->assign('PageTopic', 'Viewing Message Boxes');

	$container = create_container('skeleton.php', 'box_view.php');
	$boxes = array();
	foreach (getAdminBoxNames() as $boxTypeID => $boxName) {
		$container['box_type_id'] = $boxTypeID;
		$boxes[$boxTypeID] = array(
			'ViewHREF' => SmrSession::getNewHREF($container),
			'BoxName' => $boxName,
			'TotalMessages' => 0,
		);
	}
	$db->query('SELECT count(message_id), box_type_id
				FROM message_boxes
				GROUP BY box_type_id');
	while ($db->nextRecord()) {
		$boxes[$db->getInt('box_type_id')]['TotalMessages'] = $db->getInt('count(message_id)');
	}
	$template->assign('Boxes', $boxes);
} else {
	$boxName = getAdminBoxNames()[$var['box_type_id']];
	$template->assign('PageTopic', 'Viewing ' . $boxName);

	$template->assign('BackHREF', SmrSession::getNewHREF(create_container('skeleton.php', 'box_view.php')));
	$db->query('SELECT * FROM message_boxes WHERE box_type_id=' . $db->escapeNumber($var['box_type_id']) . ' ORDER BY send_time DESC');
	$messages = array();
	if ($db->getNumRows()) {
		$container = create_container('box_delete_processing.php');
		$container['box_type_id'] = $var['box_type_id'];
		$template->assign('DeleteHREF', SmrSession::getNewHREF($container));
		while ($db->nextRecord()) {
			$gameID = $db->getInt('game_id');
			$validGame = $gameID > 0 && Globals::isValidGame($gameID);
			$messageID = $db->getInt('message_id');
			$messages[$messageID] = array(
				'ID' => $messageID
			);

			$senderAccountID = $db->getInt('sender_account_id');
			if ($senderAccountID == 0) {
				$senderName = 'User not logged in';
			} else {
				$senderAccount = SmrAccount::getAccount($senderAccountID);
				$senderName = $senderAccount->getLogin() . ' (' . $senderAccountID . ')';
				if ($validGame) {
					$senderPlayer = SmrPlayer::getPlayerByAccountID($senderAccountID, $gameID);
					$senderName .= ' a.k.a ' . $senderPlayer->getDisplayName();
					$container = create_container('skeleton.php', 'box_reply.php');
					$container['sender_account_id'] = $senderAccountID;
					$container['game_id'] = $gameID;
					transfer('box_type_id');
					$messages[$messageID]['ReplyHREF'] = SmrSession::getNewHREF($container);
				}
			}
			$messages[$messageID]['SenderName'] = $senderName;

			if ($gameID == 0) {
				$messages[$messageID]['GameName'] = 'No game selected';
			} elseif (!$validGame) {
				$messages[$messageID]['GameName'] = 'Game no longer exists';
			} else {
				$messages[$messageID]['GameName'] = SmrGame::getGame($gameID)->getDisplayName();
			}

			$messages[$messageID]['SendTime'] = date(DATE_FULL_SHORT, $db->getInt('send_time'));
			$messages[$messageID]['Message'] = bbifyMessage(htmliseMessage($db->getField('message_text')));
		}
		$template->assign('Messages', $messages);
	}
}
