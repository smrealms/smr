<?php declare(strict_types=1);

$amount = SmrSession::getRequestVarInt('amount');
$account_id = SmrSession::getRequestVarInt('account_id');
if ($amount <= 0) {
	create_error('You can only tranfer a positive amount!');
}

if ($amount > $account->getSmrCredits()) {
	create_error('You can\'t transfer more than you have!');
}

$template->assign('PageTopic', 'Confirmation');
$template->assign('Amount', $amount);
$template->assign('HofName', SmrAccount::getAccount($account_id)->getHofDisplayName());

$container = create_container('preferences_processing.php');
$container['account_id'] = $account_id;
$container['amount'] = $amount;
$template->assign('SubmitHREF', SmrSession::getNewHREF($container));
