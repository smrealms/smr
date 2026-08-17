<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Smr\Account;
use Smr\Messages;
use Smr\Page\AccountPage;
use Smr\Template;

class BuyMessageNotifications extends AccountPage {

	public function __construct(
		private readonly ?string $message = null,
	) {}

	public function build(Account $account, Template $template): void {
		$template->pageTopic = 'Message Notifications';

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
		$template->pageRenderer = fn() => BuyMessageNotificationsRenderer::render(
			Message: $this->message,
			MessageBoxes: $messageBoxes,
		);
	}

}
