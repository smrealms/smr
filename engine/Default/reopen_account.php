<?php

// This page should only be accessed by players whose accounts
// have been closed at their own request.
$disabled = $account->isDisabled();

if ($disabled === false) {
	create_error('Your account is not disabled!');
}
if ($disabled['Reason'] != CLOSE_ACCOUNT_BY_REQUEST_REASON) {
	create_error('You are not allowed to re-open your account!');
}

$template->assign('PageTopic', 'Re-Open Account?');

// It doesn't really matter what page we link to -- the closing
// conditional will be triggered in the loader since the account
// is still banned, so we do the unbanning there.
$container = create_container('skeleton.php', 'game_play.php');
$container['do_reopen_account'] = true;
$template->assign('ReopenLink', SmrSession::getNewHREF($container));
