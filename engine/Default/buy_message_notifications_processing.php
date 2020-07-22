<?php declare(strict_types=1);

if ($account->getTotalSmrCredits() < 1) {
	create_error('You do not have enough SMR credits.');
}

$account->decreaseTotalSmrCredits(1);
$account->increaseMessageNotifications($var['MessageTypeID'], MESSAGES_PER_CREDIT[$var['MessageTypeID']]);
$account->update();

forward(create_container('skeleton.php', 'buy_message_notifications.php', array('Message' => '<span class="green">SUCCESS</span>: You have purchased ' . MESSAGES_PER_CREDIT[$var['MessageTypeID']] . ' message notifications.')));
