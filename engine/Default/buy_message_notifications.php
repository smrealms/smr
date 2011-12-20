<?php

if(isset($var['Message'])) {
	$template->assign('Message',$var['Message']);
}

$template->assign('PageTopic','Message Notifications');

$container = create_container('buy_message_notifications_processing.php');

$db->query('SELECT * FROM message_type WHERE message_type_id = ' . $db->escapeNumber(MSG_PLAYER) . ' ORDER BY message_type_id');
$messageBoxes = array ();
while ($db->nextRecord()) {
	$messageTypeID = $db->getInt('message_type_id');
	$messageBox = array();
	$messageBox['Name'] = $db->getField('message_type_name');
	
	$messageBox['MessagesRemaining'] = $account->getMessageNotifications($messageTypeID);
	$messageBox['MessagesPerCredit'] = $MESSAGES_PER_CREDIT[$messageTypeID];
	
	$container['MessageTypeID'] = $messageTypeID;
	$messageBox['BuyHref'] = SmrSession::get_new_href($container);
	$messageBoxes[] = $messageBox;
}
$template->assignByRef('MessageBoxes', $messageBoxes);

?>