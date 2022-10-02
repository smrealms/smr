<?php declare(strict_types=1);

use Smr\Messages;

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$account = $session->getAccount();

if (isset($var['Message'])) {
	$template->assign('Message', $var['Message']);
}

$template->assign('PageTopic', 'Message Notifications');

$container = Page::create('buy_message_notifications_processing.php');

// Presently only player messages are eligible for notifications
$notifyTypeIDs = [MSG_PLAYER];

$messageBoxes = [];
foreach ($notifyTypeIDs as $messageTypeID) {
	$messageBox = [];
	$messageBox['Name'] = Messages::getMessageTypeNames($messageTypeID);

	$messageBox['MessagesRemaining'] = $account->getMessageNotifications($messageTypeID);
	$messageBox['MessagesPerCredit'] = MESSAGES_PER_CREDIT[$messageTypeID];

	$container['MessageTypeID'] = $messageTypeID;
	$messageBox['BuyHref'] = $container->href();
	$messageBoxes[] = $messageBox;
}
$template->assign('MessageBoxes', $messageBoxes);
