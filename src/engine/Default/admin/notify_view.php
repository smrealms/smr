<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$account = $session->getAccount();

$template->assign('PageTopic', 'Viewing Reported Messages');

$container = Page::create('admin/notify_delete_processing.php');
$template->assign('DeleteHREF', $container->href());

$db = Smr\Database::getInstance();
$dbResult = $db->read('SELECT * FROM message_notify');
$messages = [];
foreach ($dbResult->records() as $dbRecord) {
	$gameID = $dbRecord->getInt('game_id');
	$sender = Smr\Messages::getMessagePlayer($dbRecord->getInt('from_id'), $gameID);
	$receiver = Smr\Messages::getMessagePlayer($dbRecord->getInt('to_id'), $gameID);

	$container = Page::create('admin/notify_reply.php');
	$container['offender'] = $dbRecord->getInt('from_id');
	$container['offended'] = $dbRecord->getInt('to_id');
	$container['game_id'] = $gameID;

	$getName = function(SmrPlayer|string $messagePlayer) use ($container, $account): string {
		if ($messagePlayer instanceof SmrPlayer) {
			$name = $messagePlayer->getDisplayName() . ' (Login: ' . $messagePlayer->getAccount()->getLogin() . ')';
		} else {
			$name = $messagePlayer;
		}
		// If we can send admin messages, make the names reply links
		if ($account->hasPermission(PERMISSION_SEND_ADMIN_MESSAGE)) {
			$name = create_link($container, $name);
		}
		return $name;
	};

	if (!SmrGame::gameExists($gameID)) {
		$gameName = 'Game ' . $gameID . ' no longer exists';
	} else {
		$gameName = SmrGame::getGame($gameID)->getDisplayName();
	}

	$messages[] = [
		'notifyID' => $dbRecord->getInt('notify_id'),
		'senderName' => $getName($sender),
		'receiverName' => $getName($receiver),
		'gameName' => $gameName,
		'sentDate' => date($account->getDateTimeFormat(), $dbRecord->getInt('sent_time')),
		'reportDate' => date($account->getDateTimeFormat(), $dbRecord->getInt('notify_time')),
		'text' => bbifyMessage($dbRecord->getString('text')),
	];
}
$template->assign('Messages', $messages);
