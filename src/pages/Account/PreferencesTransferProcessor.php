<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Smr\Page\AccountPageProcessor;
use Smr\Request;
use SmrAccount;

class PreferencesTransferProcessor extends AccountPageProcessor {

	public function __construct(
		private readonly int $amount,
		private readonly int $accountID
	) {}

	public function build(SmrAccount $account): never {
		$message = null;
		if (Request::getBool('action')) {
			// take from us
			$account->decreaseSmrCredits($this->amount);
			// add to recepient
			$toAccount = SmrAccount::getAccount($this->accountID);
			$toAccount->increaseSmrCredits($this->amount);

			$message = '<span class="green">SUCCESS: </span>You have sent SMR credits.';
		}

		// TODO: need a page that goes to either CurrentSector or PlayGame appropriately
		(new GamePlay(message: $message))->go();
	}

}
