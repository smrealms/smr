<?php declare(strict_types=1);

require_once(get_file_loc('messages.inc.php'));

if (!isset($var['box_type_id'])) {
	$template->assign('PageTopic', 'Viewing Message Boxes');

	$container = Page::create('skeleton.php', 'box_view.php');
	$boxes = array();
	foreach (getAdminBoxNames() as $boxTypeID => $boxName) {
		$container['box_type_id'] = $boxTypeID;
		$boxes[$boxTypeID] = array(
			'ViewHREF' => $container->href(),
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

	$template->assign('BackHREF', Page::create('skeleton.php', 'box_view.php')->href());
	$db->query('SELECT * FROM message_boxes WHERE box_type_id=' . $db->escapeNumber($var['box_type_id']) . ' ORDER BY send_time DESC');
	$messages = array();
	if ($db->getNumRows()) {
		$container = Page::create('box_delete_processing.php');
		$container['box_type_id'] = $var['box_type_id'];
		$template->assign('DeleteHREF', $container->href());
		while ($db->nextRecord()) {
			$gameID = $db->getInt('game_id');
			$validGame = $gameID > 0 && Globals::isValidGame($gameID);
			$messageID = $db->getInt('message_id');
			$messages[$messageID] = array(
				'ID' => $messageID
			);

			$senderID = $db->getInt('sender_id');
			if ($senderID == 0) {
				$senderName = 'User not logged in';
			} else {
				$senderAccount = SmrAccount::getAccount($senderID);
				$senderName = $senderAccount->getLogin() . ' (' . $senderID . ')';
				if ($validGame) {
					$senderPlayer = SmrPlayer::getPlayer($senderID, $gameID);
					$senderName .= ' a.k.a ' . $senderPlayer->getDisplayName();
					if ($account->hasPermission(PERMISSION_SEND_ADMIN_MESSAGE)) {
						$container = Page::create('skeleton.php', 'box_reply.php');
						$container['sender_id'] = $senderID;
						$container['game_id'] = $gameID;
						$container->addVar('box_type_id');
						$messages[$messageID]['ReplyHREF'] = $container->href();
					}
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
