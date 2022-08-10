<?php declare(strict_types=1);

		$template = Smr\Template::getInstance();
		$session = Smr\Session::getInstance();
		$account = $session->getAccount();

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

		$container = Page::create('preferences_processing.php');
		$container['account_id'] = $account_id;
		$container['amount'] = $amount;
		$template->assign('SubmitHREF', $container->href());
