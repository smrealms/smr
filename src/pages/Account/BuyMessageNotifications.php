<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Smr\Account;
use Smr\Messages;
use Smr\Page\AccountPage;
use Smr\Template;

class BuyMessageNotifications extends AccountPage {

	public string $file = 'buy_message_notifications.php';

	public function __construct(
		private readonly ?string $message = null,
	) {}

	public function build(Account $account, Template $template): void {
		$template->assign('Message', $this->message);

		$template->assign('PageTopic', 'Message Notifications');

		// Presently only player messages are eligible for notifications
		$notifyTypeIDs = [MSG_PLAYER];

		$messageBoxes = [];
		foreach ($notifyTypeIDs as $messageTypeID) {
			$messageBox = [];
			$messageBox['Name'] = Messages::getMessageTypeNames($messageTypeID);

			$messageBox['MessagesRemaining'] = $account->getMessageNotifications($messageTypeID);
			$messageBox['MessagesPerCredit'] = MESSAGES_PER_CREDIT[$messageTypeID];

			$container = new BuyMessageNotificationsProcessor($messageTypeID);
			$messageBox['BuyHref'] = $container->href();
			$messageBoxes[] = $messageBox;
		}
		$template->assign('MessageBoxes', $messageBoxes);
	}

}
