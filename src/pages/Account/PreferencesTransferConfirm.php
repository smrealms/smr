<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Smr\Account;
use Smr\Exceptions\AccountNotFound;
use Smr\Page\AccountPage;
use Smr\Session;
use Smr\Template;

class PreferencesTransferConfirm extends AccountPage {

	public function build(Account $account, Template $template): void {
		$session = Session::getInstance();
		$amount = $session->getRequestVarInt('amount');
		$account_id = $session->getRequestVarInt('account_id');
		if ($amount <= 0) {
			create_error('You can only tranfer a positive amount!');
		}

		if ($amount > $account->getSmrCredits()) {
			create_error('You can\'t transfer more SMR credits than you have!');
		}

		try {
			$toAccount = Account::getAccount($account_id);
		} catch (AccountNotFound) {
			create_error('That account does not exist!');
		}
		if (!$toAccount->isValidated()) {
			create_error('You cannot send SMR credits to unvalidated accounts.');
		}

		$template->pageTopic = 'Confirmation';
		$template->pageRenderer = fn() => PreferencesTransferConfirmRenderer::render(
			Amount: $amount,
			ToAccountID: $account_id,
			HofName: $toAccount->getHofDisplayName(),
			ConfirmHREF: new PreferencesTransferProcessor($amount, $account_id)->href(),
			CancelHREF: new Preferences()->href(),
		);
	}

}
