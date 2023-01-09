<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Smr\Account;
use Smr\Page\AccountPageProcessor;

class BuyMessageNotificationsProcessor extends AccountPageProcessor {

	public function __construct(
		private readonly int $messageTypeID
	) {}

	public function build(Account $account): never {
		if ($account->getTotalSmrCredits() < 1) {
			create_error('You do not have enough SMR credits.');
		}

		$account->decreaseTotalSmrCredits(1);
		$account->increaseMessageNotifications($this->messageTypeID, MESSAGES_PER_CREDIT[$this->messageTypeID]);
		$account->update();

		$message = '<span class="green">SUCCESS</span>: You have purchased ' . MESSAGES_PER_CREDIT[$this->messageTypeID] . ' message notifications.';
		(new BuyMessageNotifications($message))->go();
	}

}
