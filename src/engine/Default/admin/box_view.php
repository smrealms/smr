<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$db = Smr\Database::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$account = $session->getAccount();

if (!isset($var['box_type_id'])) {
	$template->assign('PageTopic', 'Viewing Message Boxes');

	$container = Page::create('skeleton.php', 'admin/box_view.php');
	$boxes = array();
	foreach (Smr\Messages::getAdminBoxNames() as $boxTypeID => $boxName) {
		$container['box_type_id'] = $boxTypeID;
		$boxes[$boxTypeID] = array(
			'ViewHREF' => $container->href(),
			'BoxName' => $boxName,
			'TotalMessages' => 0,
		);
	}
	$dbResult = $db->read('SELECT count(message_id), box_type_id
				FROM message_boxes
				GROUP BY box_type_id');
	foreach ($dbResult->records() as $dbRecord) {
		$boxes[$dbRecord->getInt('box_type_id')]['TotalMessages'] = $dbRecord->getInt('count(message_id)');
	}
	$template->assign('Boxes', $boxes);
} else {
	$boxName = Smr\Messages::getAdminBoxNames()[$var['box_type_id']];
	$template->assign('PageTopic', 'Viewing ' . $boxName);

	$template->assign('BackHREF', Page::create('skeleton.php', 'admin/box_view.php')->href());
	$dbResult = $db->read('SELECT * FROM message_boxes WHERE box_type_id=' . $db->escapeNumber($var['box_type_id']) . ' ORDER BY send_time DESC');
	$messages = array();
	if ($dbResult->hasRecord()) {
		$container = Page::create('admin/box_delete_processing.php');
		$container->addVar('box_type_id');
		$template->assign('DeleteHREF', $container->href());
		foreach ($dbResult->records() as $dbRecord) {
			$gameID = $dbRecord->getInt('game_id');
			$validGame = $gameID > 0 && SmrGame::gameExists($gameID);
			$messageID = $dbRecord->getInt('message_id');
			$messages[$messageID] = array(
				'ID' => $messageID
			);

			$senderID = $dbRecord->getInt('sender_id');
			if ($senderID == 0) {
				$senderName = 'User not logged in';
			} else {
				$senderAccount = SmrAccount::getAccount($senderID);
				$senderName = $senderAccount->getLogin() . ' (' . $senderID . ')';
				if ($validGame) {
					$senderPlayer = SmrPlayer::getPlayer($senderID, $gameID);
					$senderName .= ' a.k.a ' . $senderPlayer->getDisplayName();
					if ($account->hasPermission(PERMISSION_SEND_ADMIN_MESSAGE)) {
						$container = Page::create('skeleton.php', 'admin/box_reply.php');
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

			$messages[$messageID]['SendTime'] = date($account->getDateTimeFormat(), $dbRecord->getInt('send_time'));
			$messages[$messageID]['Message'] = bbifyMessage(htmliseMessage($dbRecord->getString('message_text')));
		}
		$template->assign('Messages', $messages);
	}
}
