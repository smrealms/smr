<?php

$template->assign('PageTopic','Viewing Message Boxes');

if(!isset($var['box_type_id'])) {
	$db->query('SELECT count(message_id), box_type_name, box_type_id
				FROM message_box_types
				LEFT JOIN message_boxes USING(box_type_id)
				GROUP BY box_type_id, box_type_name');
	$container = create_container('skeleton.php', 'box_view.php');
	$boxes = array();
	while($db->nextRecord()) {
		$boxTypeID = $db->getInt('box_type_id');
		$container['box_type_id'] = $boxTypeID;
		$boxes[$boxTypeID] = array(
			'ViewHREF' => SmrSession::getNewHREF($container),
			'BoxName' => $db->getField('box_type_name'),
			'TotalMessages' => $db->getField('count(message_id)')
		);
	}
	$template->assign('Boxes', $boxes);
}
else {
	$template->assign('BackHREF', SmrSession::getNewHREF(create_container('skeleton.php','box_view.php')));
	$db->query('SELECT * FROM message_boxes WHERE box_type_id='.$db->escapeNumber($var['box_type_id']).' ORDER BY send_time DESC');
	$messages = array();
	if ($db->getNumRows()) {
		$container = create_container('box_delete_processing.php');
		$container['box_type_id'] = $var['box_type_id'];
		$template->assign('DeleteHREF', SmrSession::getNewHREF($container));
		while($db->nextRecord()) {
			$gameID = $db->getInt('game_id');
			$validGame = $gameID > 0 && Globals::isValidGame($gameID);
			$messageID = $db->getInt('message_id');
			$messages[$messageID] = array(
				'ID' => $messageID
			);
			$senderAccount =& SmrAccount::getAccount($db->getField('sender_id'));
			$senderName = $senderAccount->getLogin().' ('.$senderAccount->getAccountID().')';
			if ($validGame) {
				$senderPlayer =& SmrPlayer::getPlayer($senderAccount->getAccountID(), $gameID);
				if($senderAccount->getLogin() != $senderPlayer->getPlayerName()) {
					$senderName .= ' a.k.a ' . $senderPlayer->getPlayerName();
				}
				
				$container = create_container('skeleton.php', 'box_reply.php');
				$container['sender_id'] = $senderAccount->getAccountID();
				$container['game_id'] = $gameID;
				$messages[$messageID]['ReplyHREF'] = SmrSession::getNewHREF($container);
			}
			$messages[$messageID]['SenderName'] = $senderName;
			if (!$validGame) {
				$messages[$messageID]['GameName'] = 'Game no longer exists';
			}
			else {
				$messages[$messageID]['GameName'] = Globals::getGameName($gameID);
			}
			$messages[$messageID]['SendTime'] = date(DATE_FULL_SHORT, $db->getField('send_time'));
			$messages[$messageID]['Message'] = bbifyMessage(htmliseMessage($db->getField('message_text')));
		}
		$template->assign('Messages', $messages);
	}
}
?>