<?php
require_once(get_file_loc('message.functions.inc'));

if (isset($var['Message'])) {
	$template->assign('Message', $var['Message']);
}

$template->assign('PageTopic', 'Message Notifications');

$container = create_container('buy_message_notifications_processing.php');

// Presently only player messages are eligible for notifications
$notifyTypeIDs = array(MSG_PLAYER);

$messageBoxes = array();
foreach ($notifyTypeIDs as $messageTypeID) {
	$messageBox = array();
	$messageBox['Name'] = getMessageTypeNames($messageTypeID);
	
	$messageBox['MessagesRemaining'] = $account->getMessageNotifications($messageTypeID);
	$messageBox['MessagesPerCredit'] = MESSAGES_PER_CREDIT[$messageTypeID];
	
	$container['MessageTypeID'] = $messageTypeID;
	$messageBox['BuyHref'] = SmrSession::getNewHREF($container);
	$messageBoxes[] = $messageBox;
}
$template->assign('MessageBoxes', $messageBoxes);
