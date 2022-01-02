<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$var = Smr\Session::getInstance()->getCurrentVar();

$boxName = Smr\Messages::getAdminBoxNames()[$var['box_type_id']];
$template->assign('PageTopic', 'Reply To ' . $boxName);

$container = Page::create('admin/box_reply_processing.php');
$container->addVar('game_id');
$container->addVar('sender_id');
$container->addVar('box_type_id');
$template->assign('BoxReplyFormHref', $container->href());
$template->assign('Sender', SmrPlayer::getPlayer($var['sender_id'], $var['game_id']));
$template->assign('SenderAccount', SmrAccount::getAccount($var['sender_id']));
if (isset($var['Preview'])) {
	$template->assign('Preview', $var['Preview']);
}
$template->assign('BanPoints', $var['BanPoints'] ?? 0);
$template->assign('RewardCredits', $var['RewardCredits'] ?? 0);

$container = Page::create('skeleton.php', 'admin/box_view.php');
$container->addVar('box_type_id');
$template->assign('BackHREF', $container->href());
