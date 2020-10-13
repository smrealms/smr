<?php declare(strict_types=1);

require_once(get_file_loc('message.functions.inc'));
$boxName = getAdminBoxNames()[$var['box_type_id']];
$template->assign('PageTopic', 'Reply To ' . $boxName);

$container = create_container('box_reply_processing.php');
transfer('game_id');
transfer('sender_account_id');
transfer('box_type_id');
$template->assign('BoxReplyFormHref', SmrSession::getNewHREF($container));
$template->assign('Sender', SmrPlayer::getPlayer($var['sender_account_id'], $var['game_id']));
$template->assign('SenderAccount', SmrAccount::getAccount($var['sender_account_id']));
if (isset($var['Preview'])) {
	$template->assign('Preview', $var['Preview']);
}
$template->assign('BanPoints', $var['BanPoints'] ?? 0);
$template->assign('RewardCredits', $var['RewardCredits'] ?? 0);

$container = create_container('skeleton.php', 'box_view.php');
transfer('box_type_id');
$template->assign('BackHREF', SmrSession::getNewHREF($container));
