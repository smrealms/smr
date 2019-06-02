<?php

$amount = SmrSession::getRequestVar('amount');
$account_id = SmrSession::getRequestVar('account_id');
if (!is_numeric($amount)) {
	create_error('Numbers only please!');
}
if (!is_numeric($account_id)) {
	create_error('Invalid player selected!');
}
$amount = round($amount);
if ($amount <= 0) {
	create_error('You can only tranfer a positive amount!');
}

if ($amount > $account->getSmrCredits()) {
	create_error('You can\'t transfer more than you have!');
}

$template->assign('PageTopic', 'Confirmation');
$template->assign('Amount', $amount);
$template->assign('HofName', SmrAccount::getAccount($account_id)->getHofName());

$container = create_container('preferences_processing.php');
$container['account_id'] = $account_id;
$container['amount'] = $amount;
$template->assign('SubmitHREF', SmrSession::getNewHREF($container));
