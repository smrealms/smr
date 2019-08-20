<?php declare(strict_types=1);

$template->assign('PageTopic', 'Invalid E-mail Address');

// This page should only be accessed by players whose accounts
// have been closed due to an invalid e-mail.
$disabled = $account->isDisabled();
if (!$disabled || $disabled['Reason'] != CLOSE_ACCOUNT_INVALID_EMAIL_REASON) {
	throw new Exception('Account not disabled for invalid email');
}

// It doesn't really matter what page we link to -- the closing
// conditional will be triggered in the loader since the account
// is still banned, so we do the unbanning there.
$container = create_container('skeleton.php', 'game_play.php');
$container['do_reopen_account'] = true;
$template->assign('ReopenLink', SmrSession::getNewHREF($container));
