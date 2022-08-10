<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Smr\Page\AccountPage;
use Smr\Session;
use Smr\Template;
use SmrAccount;

class PreferencesTransferConfirm extends AccountPage {

	public string $file = 'preferences_confirm.php';

	public function build(SmrAccount $account, Template $template): void {
		$session = Session::getInstance();
		$amount = $session->getRequestVarInt('amount');
		$account_id = $session->getRequestVarInt('account_id');
		if ($amount <= 0) {
			create_error('You can only tranfer a positive amount!');
		}

		if ($amount > $account->getSmrCredits()) {
			create_error('You can\'t transfer more than you have!');
		}

		$template->assign('PageTopic', 'Confirmation');
		$template->assign('Amount', $amount);
		$template->assign('HofName', SmrAccount::getAccount($account_id)->getHofDisplayName());

		$container = new PreferencesTransferProcessor($amount, $account_id);
		$template->assign('SubmitHREF', $container->href());
	}

}
